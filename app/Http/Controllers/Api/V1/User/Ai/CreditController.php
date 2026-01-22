<?php

namespace App\Http\Controllers\Api\V1\User\Ai;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CreditController extends Controller
{
    public function status(Request $request)
    {
        $user = $request->user();
        $freeLimit = Setting::whereKey('free_credits_limit')->value('value');
        $freeUsed = $user->free_credits_used;
        $freeLeft = max(0, $freeLimit - $freeUsed);
        return Response::api(data:[
            'free_credits' => [
                'limit' => $freeLimit,
                'used' => $freeUsed,
                'left' => $freeLeft,
            ],
            'wallet_credits' => $user->wallet?->balance,
            'total_credits_left' => $freeLeft + $user->wallet?->balance,
        ]);
    }
}
