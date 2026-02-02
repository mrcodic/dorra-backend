<?php

namespace App\Services\Wallet;


use App\Models\User;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;


class WalletService extends BaseService
{
    public static function credit(User $user, int $credits, string $source = "purchase"): void
    {
        DB::transaction(function () use ($user, $credits, $source) {
            $wallet = $user->wallet()->lockForUpdate()->first();
            $wallet->increment('balance', $credits);
            $wallet->walletTransactions()->create([
                'amount' => +$credits,
                'source' => $source,
            ]);
        });
    }

    public static function debit(User $user, int $credits, string $source = "logo_generation"): void
    {
        DB::transaction(function () use ($user, $credits, $source) {
            $wallet = $user->wallet()->lockForUpdate()->first();
            $wallet->decrement('balance', $credits);
            $wallet->walletTransactions()->create([
                'amount' => -$credits,
                'source' => $source,
            ]);
        });
    }

    public static function reserve(User $user, int $credits, string $source = 'ai_reserve'): void
    {
        DB::transaction(function () use ($user, $credits, $source) {
            $wallet = $user->wallet()->lockForUpdate()->firstOrFail();
//            if (($wallet->balance - $wallet->reserved_balance) < $credits) {
//                throw new \Exception("Insufficient available credits");
//            }

            $wallet->increment('reserved_balance', $credits);

            $wallet->walletTransactions()->create([
                'amount' => 0,
                'reserved' => $credits,
                'source' => $source,
                'type' => 'reserve'
            ]);
        });
    }
    public static function capture(User $user, int $credits, string $source = 'ai_capture'): void
    {
        DB::transaction(function () use ($user, $credits, $source) {
            $wallet = $user->wallet()->lockForUpdate()->firstOrFail();

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
            $wallet = $user->wallet()->lockForUpdate()->firstOrFail();

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
