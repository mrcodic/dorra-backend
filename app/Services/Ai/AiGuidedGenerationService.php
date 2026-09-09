<?php

namespace App\Services\Ai;

use App\Models\Setting;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AiGuidedGenerationService
{
    public function __construct(
        private readonly AiPromptBuilderService $aiPromptBuilderService,
        private readonly GenAiImageService $genAiImageService,
    ) {
    }

    public function generate($user, int $aiCategoryId, int $aiStudioItemId, array $answers = []): array {/*
         * Build prompt from:
         * Category + Studio Item + Answers
         */
        $built = $this->aiPromptBuilderService->build($aiCategoryId, $aiStudioItemId, $answers);


        $creditsCost = (int) data_get(
            $built,
            'generation.credits_cost',
            1
        );

        $freeLimit = (int) Setting::where(
            'key',
            'free_credits_limit'
        )->value('value');

        $reserved = [
            'free' => 0,
            'wallet' => 0,
        ];

        $transactionType = 'ai_guided_generation';

        try {
            DB::beginTransaction();

            $lockedUser = $user
                ->newQuery()
                ->lockForUpdate()
                ->findOrFail($user->id);

            $wallet = $lockedUser
                ->wallet()
                ->lockForUpdate()
                ->first();

            $availableWallet = max(
                0,
                ($wallet?->balance ?? 0)
                - ($wallet?->reserved_balance ?? 0)
            );

            $freeLeft = max(
                0,
                $freeLimit
                - (int) $lockedUser->free_credits_used
            );

            if (
                ($freeLeft + $availableWallet)
                < $creditsCost
            ) {
                DB::rollBack();

                throw ValidationException::withMessages([
                    'payment' => [
                        'Insufficient credits.',
                    ],
                ]);
            }

            /*
             * Free credits first.
             */
            if ($freeLeft > 0) {
                $useFree = min(
                    $freeLeft,
                    $creditsCost
                );

                if ($useFree > 0) {
                    $lockedUser->increment(
                        'free_credits_used',
                        $useFree
                    );

                    $reserved['free'] = $useFree;
                }
            }

            /*
             * Remaining from wallet.
             */
            $walletNeeded =
                $creditsCost
                - $reserved['free'];

            if ($walletNeeded > 0) {
                WalletService::reserve(
                    $lockedUser,
                    $walletNeeded,
                    $transactionType
                );

                $reserved['wallet'] =
                    $walletNeeded;
            }

            DB::commit();
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            throw $e;
        }

        /*
         * ================= AI CALL =================
         */
        try {
            $result = $this->genAiImageService->generate(
                prompt: $built['prompt'],

                negativePrompt:
                $built['negative_prompt']
                    ?: null,

                transparentBackground:
                (bool) data_get(
                    $built,
                    'generation.transparent_background',
                    false
                )
            );
        } catch (\Throwable $e) {
            $this->releaseCredits(
                $user,
                $reserved,
                $transactionType . '_exception'
            );

            throw $e;
        }

        /*
         * ================= FAILURE =================
         */
        if (!($result['ok'] ?? false)) {
            $this->releaseCredits(
                $user,
                $reserved,
                $transactionType . '_fail'
            );

            throw ValidationException::withMessages([
                'generation' => [
                    $result['error']
                    ?? 'Image generation failed.',
                ],
            ]);
        }

        /*
         * ================= SUCCESS =================
         */
        if ($reserved['wallet'] > 0) {
            DB::transaction(function () use (
                $user,
                $reserved,
                $transactionType
            ) {
                $lockedUser = $user
                    ->newQuery()
                    ->lockForUpdate()
                    ->findOrFail($user->id);

                WalletService::capture(
                    $lockedUser,
                    $reserved['wallet'],
                    $transactionType
                );
            });
        }

        return [
            'images' =>
                $result['images'],

            'model' =>
                $result['model'] ?? null,

            'usage' =>
                $result['usage'] ?? null,

            'promptFeedback' =>
                $result['promptFeedback'] ?? null,

            'arabicNote' =>
                $result['arabicNote'] ?? null,

            'credits' => [
                'cost' =>
                    $creditsCost,

                'free_used' =>
                    $reserved['free'],

                'wallet_used' =>
                    $reserved['wallet'],
            ],
        ];
    }

    private function releaseCredits($user, array $reserved, string $reason): void
    {
        DB::transaction(function () use (
            $user,
            $reserved,
            $reason
        ) {
            $lockedUser = $user
                ->newQuery()
                ->lockForUpdate()
                ->findOrFail($user->id);

            if ($reserved['free'] > 0) {
                $lockedUser->decrement(
                    'free_credits_used',
                    $reserved['free']
                );
            }

            if ($reserved['wallet'] > 0) {
                WalletService::release(
                    $lockedUser,
                    $reserved['wallet'],
                    $reason
                );
            }
        });
    }
}
