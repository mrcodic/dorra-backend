<?php

namespace App\Http\Controllers\Api\V1\User\Ai;

use App\Enums\HttpEnum;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Ai\GenAiImageService;
use App\Services\Wallet\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class CreditController extends Controller
{
    public function status(Request $request)
    {
        $user = $request->user();
        $freeLimit = Setting::where('key', 'free_credits_limit')->value('value');
        $freeUsed = $user->free_credits_used;
        $freeLeft = max(0, $freeLimit - $freeUsed);

        $walletBalance = $user->wallet?->balance ?? 0;

        $walletUsed = $user->wallet
            ? (int)$user->wallet->walletTransactions()
                ->where('type', 'debit')
                ->sum('amount') * -1
            : 0;
        return Response::api(data: [
            'free_credits' => [
                'used' => $freeUsed,
                'available' => $freeLeft,
                'total' => $freeLimit,
            ],
            'wallet_credits' => [
                'used' => $walletUsed,
                'available' => $walletBalance,
                'total' => $walletUsed + $walletBalance,
            ],
            'used_credits' => $freeUsed + $walletUsed,
            'available_credits' => $freeLeft + $walletBalance,
            'total_credits' => $freeLeft + $walletBalance + $freeUsed + $walletUsed,
        ]);
    }

    public function generateImage(Request $request, GenAiImageService $genAiImageService)
    {
        $data = $request->validate([
            'prompt' => ['required', 'string'],
            'negative_prompt' => ['nullable', 'string'],
        ]);

        $apiKey = config('services.google_genai.api_key');
        if (!$apiKey) {
            return response()->json(['error' => ['Missing GOOGLE_API_KEY']], 500);
        }


        $res = $genAiImageService->generate($data['prompt'], $data['negative_prompt'] ?? null);

        if (!$res['ok']) {
            return Response::api(status: $res['status'], errors: [
                'error' => [$res['error']]
            ]);
        }

        return Response::api([
            'images' => $res['images'],
            'model' => $res['model'],
            'usage' => $res['usage'],
            'promptFeedback' => $res['promptFeedback'],
            'arabicNote' => $res['arabicNote'],
        ]);
    }

    public function consume(Request $request)
    {
        $request->validate([
            'tokens' => 'required|integer|min:1',
            'type' => ['required', 'in:logo_generation,image_generation']
        ]);

        $user = $request->user();

        $tokensPerCredit = (int)Setting::where('key', 'tokens_per_credit')->value('value');
        $freeLimit = (int)Setting::where('key', 'free_credits_limit')->value('value');

        $creditsNeeded = (int)ceil($request->tokens / $tokensPerCredit);


        $freeLeft = max(0, $freeLimit - $user->free_credits_used);

        if ($freeLeft + $user->wallet->balance < $creditsNeeded) {
            return Response::api(HttpEnum::PAYMENT_REQUIRED, "Insufficient credits", errors: [
                "payment" => "Insufficient credits"
            ]);
        }

        DB::transaction(function () use ($user, $creditsNeeded, $freeLeft, $request) {

            if ($freeLeft > 0) {
                $useFree = min($freeLeft, $creditsNeeded);
                $user->increment('free_credits_used', $useFree);
                $creditsNeeded -= $useFree;
            }

            if ($creditsNeeded > 0) {
                WalletService::debit($user, $creditsNeeded, $request->type);
            }
        });

        return Response::api(data: [
            'credits_used' => (int)ceil($request->tokens / $tokensPerCredit)
        ]);
    }

}
