<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

function getActiveGuard(): string|null
{
    foreach (array_keys(config('auth.guards')) as $guard) {
        if (Auth::guard($guard)->check()) {
            return $guard;
        }
    }
    return null;
}

function getOrderStepCacheKey(): string
{
    return 'order_step_data_1';

    if (getActiveGuard() == "web" && request()->has('user_id')) {
        return 'order_step_data_' . request('user_id');
    }
    if (auth('sanctum')->check()) {
        return 'order_step_data_' . auth()->id();
    }

    $cookieId = request()->cookie('cookie_id') ?? (string)Str::uuid();

    if (!request()->hasCookie('cookie_id')) {
        cookie()->queue(cookie('cookie_id', $cookieId, 60 * 24 * 30));
    }

    return 'order_step_data_guest_' . $cookieId;
}

function getDiscountAmount($discount, $subtotal): float|int
{
    return $subtotal * $discount;
}

function getTotalPrice($discount, $subtotal): float|int
{
    $discountAmount = getDiscountAmount($discount, $subtotal);
    $tax = 0.1;
    $delivery = 30;
    return $subtotal - $discountAmount + ($tax * $subtotal) + $delivery;
}
