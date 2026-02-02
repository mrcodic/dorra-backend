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
            'type' => ['required', 'in:logo_generation,image_generation']
        ]);

        $user = $request->user();

        $tokensPerCredit = (int) Setting::where('key', 'tokens_per_credit')->value('value');
        $freeLimit       = (int) Setting::where('key', 'free_credits_limit')->value('value');

        $estimatedTokens = $genAiImageService->estimateTokens(
            $data['prompt'],
            $data['negative_prompt'] ?? null,
            outputImages: 1,
            hasInputImage: false
        );

        $estimatedCredits = (int) ceil($estimatedTokens / max(1, $tokensPerCredit));
        $reserved = ['free' => 0, 'wallet' => 0];

        /** ================= RESERVE PHASE ================= */
        DB::beginTransaction();
        try {
            $lockedUser = $user->newQuery()->lockForUpdate()->findOrFail($user->id);
            $wallet = $lockedUser->wallet()->lockForUpdate()->firstOrFail();

            $availableWallet = $wallet->balance - $wallet->reserved_balance;
            $freeLeft = max(0, $freeLimit - (int)$lockedUser->free_credits_used);
            if ($freeLeft + $availableWallet < $estimatedCredits) {
                DB::rollBack();
                return Response::api(HttpEnum::PAYMENT_REQUIRED, "Insufficient credits", errors: [
                    "payment" => "Insufficient credits"
                ]);
            }

            // Use free credits first
            if ($freeLeft > 0) {
                $useFree = min($freeLeft, $estimatedCredits);
                $lockedUser->increment('free_credits_used', $useFree);
                $reserved['free'] = $useFree;
                $estimatedCredits -= $useFree;
            }

            // Reserve wallet credits
            if ($estimatedCredits > 0) {
                WalletService::reserve($lockedUser, $estimatedCredits, 'image_generation');
                $reserved['wallet'] = $estimatedCredits;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        /** ================= AI CALL ================= */
        $res = $genAiImageService->generate($data['prompt'], $data['negative_prompt'] ?? null);

        /** ================= FAILURE → RELEASE ================= */
        if (!$res['ok']) {
            DB::transaction(function () use ($user, $reserved) {
                $lockedUser = $user->newQuery()->lockForUpdate()->findOrFail($user->id);

                if ($reserved['free'] > 0) {
                    $lockedUser->decrement('free_credits_used', $reserved['free']);
                }

                if ($reserved['wallet'] > 0) {
                    WalletService::release($lockedUser, $reserved['wallet'], 'image_generation_fail');
                }
            });

            return Response::api(status: $res['status'], errors: [
                'error' => [$res['error']]
            ]);
        }

        /** ================= RECONCILE ================= */
        $actualTokens = (int) data_get($res, 'usage.totalTokenCount', 0);

        if ($actualTokens > 0) {
            $actualCredits = (int) ceil($actualTokens / max(1, $tokensPerCredit));

            DB::transaction(function () use ($user, $actualCredits, $reserved) {
                $lockedUser = $user->newQuery()->lockForUpdate()->findOrFail($user->id);

                $walletReserved = $reserved['wallet'];

                if ($walletReserved > 0) {
                    if ($actualCredits <= $walletReserved) {
                        WalletService::capture($lockedUser, $actualCredits, 'image_generation');
                        WalletService::release($lockedUser, $walletReserved - $actualCredits, 'image_generation_adjust');
                    } else {
                        // Need extra credits beyond reserved
                        $extra = $actualCredits - $walletReserved;
                        WalletService::capture($lockedUser, $walletReserved, 'image_generation');
                        WalletService::debit($lockedUser, $extra, 'image_generation_overuse');
                    }
                }
            });
        }

        return Response::api(data: [
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
