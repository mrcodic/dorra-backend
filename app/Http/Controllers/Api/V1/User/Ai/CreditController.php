<?php

namespace App\Http\Controllers\Api\V1\User\Ai;

use App\Enums\HttpEnum;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Transaction;
use App\Services\Ai\GenAiImageService;
use App\Services\Wallet\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class CreditController extends Controller
{
    public function status(Request $request)
    {
        $freeLimit = (int) Setting::where('key', 'free_credits_limit')->value('value');
        $user = $request->user();
        $freeUsed = (int)($user->free_credits_used ?? 0);
        $wallet = $user->wallet;
        $walletUsed = $wallet
            ? (int)$wallet->walletTransactions()
                ->where(function ($query) {
                    $query->where('type', 'debit')
                        ->orWhere('type', 'capture');
                })
                ->sum('amount') * -1
            : 0;

        return Response::api(data: [
            'used_credits' => $freeUsed + $walletUsed,
                'available_credits' => $user->available_credits,
            'total_credits' => $user->total_credits,
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

        $tokensPerCredit = (int)Setting::where('key', 'tokens_per_credit')->value('value');
        $freeLimit = (int)Setting::where('key', 'free_credits_limit')->value('value');

        $estimatedTokens = $genAiImageService->estimateTokens(
            $data['prompt'],
            $data['negative_prompt'] ?? null,
            outputImages: 1,
            hasInputImage: false
        );

        $estimatedCredits = (int)ceil($estimatedTokens / max(1, $tokensPerCredit));
        $reserved = ['free' => 0, 'wallet' => 0];

        /** ================= RESERVE PHASE ================= */
        try {
            DB::beginTransaction();

            $lockedUser = $user->newQuery()->lockForUpdate()->findOrFail($user->id);
            $wallet = $lockedUser->wallet()->lockForUpdate()->first();

            $availableWallet = max(0, $wallet?->balance - $wallet?->reserved_balance);
            $freeLeft = max(0, $freeLimit - (int)$lockedUser->free_credits_used);

            if (($freeLeft + $availableWallet) < $estimatedCredits) {
                return Response::api(HttpEnum::PAYMENT_REQUIRED, "Insufficient credits", errors: [
                    "payment" => "Insufficient credits"
                ]);
            }

            // Use free credits first
            if ($freeLeft > 0) {
                $useFree = min($freeLeft, $estimatedCredits);
                $lockedUser->increment('free_credits_used', $useFree);
                $lockedUser->decrement('available_credits', $useFree);
                $reserved['free'] = $useFree;
                $estimatedCredits -= $useFree;
            }

            // Reserve wallet credits
            if ($estimatedCredits > 0) {
                WalletService::reserve($lockedUser, $estimatedCredits, $data['type']);
                $reserved['wallet'] = $estimatedCredits;
            }

            DB::commit();

        } catch (\RuntimeException $e) {
            DB::rollBack();
            return Response::api(HttpEnum::PAYMENT_REQUIRED, "Insufficient credits", errors: [
                "payment" => "Insufficient credits"
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        /** ================= AI CALL ================= */
        try {
            $res = $genAiImageService->generate($data['prompt'], $data['negative_prompt'] ?? null);
        } catch (\Throwable $e) {
            DB::transaction(function () use ($user, $reserved, $data) {
                $lockedUser = $user->newQuery()->lockForUpdate()->findOrFail($user->id);

                if ($reserved['free'] > 0) {
                    $lockedUser->decrement('free_credits_used', $reserved['free']);
                }

                if ($reserved['wallet'] > 0) {
                    WalletService::release($lockedUser, $reserved['wallet'], $data['type'] . '_exception');
                }
            });

            throw $e;
        }

        /** ================= FAILURE → RELEASE ================= */
        if (!$res['ok']) {
            DB::transaction(function () use ($user, $reserved, $data) {
                $lockedUser = $user->newQuery()->lockForUpdate()->findOrFail($user->id);

                if ($reserved['free'] > 0) {
                    $lockedUser->decrement('free_credits_used', $reserved['free']);
                }

                if ($reserved['wallet'] > 0) {
                    WalletService::release($lockedUser, $reserved['wallet'], $data['type'] . '_fail');
                }
            });

            return Response::api(status: $res['status'], errors: [
                'error' => [$res['error']]
            ]);
        }

        /** ================= RECONCILE ================= */
        $actualTokens = (int)data_get($res, 'usage.totalTokenCount', 0);

        if ($actualTokens > 0) {
            $actualCredits = (int)ceil($actualTokens / max(1, $tokensPerCredit));

            DB::transaction(function () use ($user, $actualCredits, $reserved, $freeLimit, $data) {

                $lockedUser = $user->newQuery()->lockForUpdate()->findOrFail($user->id);
                $wallet = $lockedUser->wallet()->lockForUpdate()->first();

                $totalReserved = $reserved['free'] + $reserved['wallet'];

                // ===== USED <= RESERVED =====
                if ($actualCredits <= $totalReserved) {

                    $remainingToCharge = $actualCredits;

                    $freeUsed = min($reserved['free'], $remainingToCharge);
                    $unusedFree = $reserved['free'] - $freeUsed;

                    $remainingToCharge -= $freeUsed;

                    if ($remainingToCharge > 0) {
                        WalletService::capture($lockedUser, $remainingToCharge, $data['type']);
                    }

                    $walletNeeded = max(0, $actualCredits - $reserved['free']);
                    $walletExcess = $reserved['wallet'] - $walletNeeded;
                    if ($walletExcess > 0) {
                        WalletService::release($lockedUser, $walletExcess, $data['type'] . '_adjust');
                    }

                    if ($unusedFree > 0) {
                        $lockedUser->decrement('free_credits_used', $unusedFree);
                    }
                } // ===== USED > RESERVED =====
                else {

                    $extraNeeded = $actualCredits - $totalReserved;

                    if ($reserved['wallet'] > 0) {
                        WalletService::capture($lockedUser, $reserved['wallet'], $data['type']);
                    }

                    $freeLeft = max(0, $freeLimit - (int)$lockedUser->free_credits_used);
                    $useExtraFree = min($freeLeft, $extraNeeded);

                    if ($useExtraFree > 0) {
                        $lockedUser->increment('free_credits_used', $useExtraFree);
                        $extraNeeded -= $useExtraFree;
                    }

                    if ($extraNeeded > 0) {
                        WalletService::debit($lockedUser, $extraNeeded, $data['type'] . '_overuse');
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


}
