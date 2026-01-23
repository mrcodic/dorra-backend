<?php

namespace App\Http\Controllers\Api\V1\User\Plan;

use App\Http\Controllers\Controller;

use App\Http\Resources\PlanResource;
use App\Models\Plan;
use App\Models\Transaction;
use App\Services\PlanService;
use App\Services\Wallet\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PlanController extends Controller
{
    public function __construct(public PlanService $planService)
    {
    }

    public function __invoke()
    {
        $activePlans = $this->planService->activePlans();
        return Response::api(data: PlanResource::collection($activePlans));
    }

    public function subscribe(Request $request)
    {
        $validateData = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id']
        ]);
        $data = $this->planService->subscribe($validateData);
        return Response::api(message: "Subscribed successfully", data: $data);
    }

    public function transaction($transaction)
    {
        $transaction = Transaction::whereTrasactionId($transaction)->first();
        if ($transaction->payable_type == Plan::class) {
            WalletService::credit($transaction->user
                ,$transaction->payable->price,
                "purchase plan {$transaction->payable->name}");
        }
        return "done";
    }
}
