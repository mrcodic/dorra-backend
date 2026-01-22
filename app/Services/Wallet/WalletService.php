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


}
