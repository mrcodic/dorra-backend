<?php

namespace App\Http\Controllers\Api\V1\User\Wallet;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use App\Http\Resources\Wallet\WalletResource;
use App\Models\Plan;
use App\Models\Transaction;
use App\Services\PlanService;
use App\Services\Wallet\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class WalletController extends Controller
{
    public function __construct(public PlanService $planService)
    {
    }

    public function __invoke()
    {
        $wallet = auth()->user()->wallet?->load('walletTransactions');
        return Response::api(data: $wallet ? WalletResource::make($wallet):[]);
    }

}
