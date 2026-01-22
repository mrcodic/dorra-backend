<?php

namespace App\Http\Controllers\Api\V1\User\Ai;

use App\Enums\HttpEnum;
use App\Http\Controllers\Controller;
use App\Models\Setting;
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
            ? (int) $user->wallet->walletTransactions()
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
            'total_credits_available' => $freeLeft + $user->wallet?->balance,
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
