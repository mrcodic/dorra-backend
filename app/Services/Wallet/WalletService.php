<?php

namespace App\Services\Wallet;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Get the user's wallet and lock it, creating it if missing
     */
    protected static function getOrCreateWallet(User $user)
    {
        $wallet = $user->wallet()->lockForUpdate()->first();

        if (!$wallet) {
            $wallet = $user->wallet()->create([
                'balance' => 0,
                'reserved_balance' => 0,
            ]);
        }

        return $wallet;
    }

    public static function credit(User $user, int $credits, string $source = "purchase"): void
    {
        DB::transaction(function () use ($user, $credits, $source) {
            $wallet = self::getOrCreateWallet($user);
            $wallet->increment('balance', $credits);

            $wallet->walletTransactions()->create([
                'amount' => +$credits,
                'reserved' => 0,
                'source' => $source,
                'type' => 'credit'
            ]);
            $freeLimit = (int) Setting::where('key', 'free_credits_limit')->value('value');
            $freeUsed  = (int) ($user->free_credits_used ?? 0);
            $walletUsed = $wallet
                ? (int)$wallet->walletTransactions()
                    ->where(function ($query) {
                        $query->where('type', 'debit')
                            ->orWhere('type', 'capture');
                    })
                    ->sum('amount') * -1
                : 0;

            $walletCredited = $wallet
                ? (int) $wallet->walletTransactions()
                    ->where('type', 'credit')
                    ->sum('amount')
                : 0;
            $totalCredits = $freeLimit + $walletCredited;
            $availableCredits = $totalCredits - $freeUsed - $walletUsed;
            $totalCredits = ($totalCredits - $availableCredits) + $totalCredits;
            $user->update([
                'total_credits' => $totalCredits,
                'available_credits' => $availableCredits,
            ]);


        });
    }

    public static function debit(User $user, int $credits, string $source = "usage"): void
    {
        DB::transaction(function () use ($user, $credits, $source) {
            $wallet = self::getOrCreateWallet($user);

            $available = $wallet->balance - $wallet->reserved_balance;

            if ($available < $credits) {
                throw new \Exception("Insufficient available credits");
            }

            $wallet->decrement('balance', $credits);
            $user->decrement('available_credits', $credits);
            $wallet->walletTransactions()->create([
                'amount' => -$credits,
                'reserved' => 0,
                'source' => $source,
                'type' => 'debit'
            ]);
        });
    }

    public static function reserve(User $user, int $credits, string $source = 'ai_reserve'): void
    {
        DB::transaction(function () use ($user, $credits, $source) {
            $wallet = self::getOrCreateWallet($user);

            $available = $wallet->balance - $wallet->reserved_balance;

            if ($available < $credits) {
                throw new \Exception("Insufficient available credits");
            }

            $wallet->increment('reserved_balance', $credits);

            $wallet->walletTransactions()->create([
                'amount' => 0,
                'reserved' => +$credits,
                'source' => $source,
                'type' => 'reserve'
            ]);
        });
    }

    public static function capture(User $user, int $credits, string $source = 'ai_capture'): void
    {
        DB::transaction(function () use ($user, $credits, $source) {
            $wallet = self::getOrCreateWallet($user);

            if ($wallet->reserved_balance < $credits) {
                throw new \LogicException("Capture exceeds reserved balance");
            }

            $wallet->decrement('reserved_balance', $credits);
            $wallet->decrement('balance', $credits);

            $wallet->walletTransactions()->create([
                'amount' => -$credits,
                'reserved' => -$credits,
                'source' => $source,
                'type' => 'capture'
            ]);
        });
    }

    public static function release(User $user, int $credits, string $source = 'ai_release'): void
    {
        DB::transaction(function () use ($user, $credits, $source) {
            $wallet = self::getOrCreateWallet($user);

            if ($wallet->reserved_balance < $credits) {
                throw new \LogicException("Release exceeds reserved balance");
            }

            $wallet->decrement('reserved_balance', $credits);

            $wallet->walletTransactions()->create([
                'amount' => 0,
                'reserved' => -$credits,
                'source' => $source,
                'type' => 'release'
            ]);
        });
    }
}
