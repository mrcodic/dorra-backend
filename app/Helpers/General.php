<?php


use App\Models\{Design, Template};
use App\Repositories\Implementations\SettingRepository;
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

function getCookie($key): array
{
    $cookieValue = request()->cookie($key);
    $cookie = null;
    if (empty($cookieValue) || $cookieValue === 'null') {
        $cookieValue = (string)Str::uuid();
        $cookie = cookie(
            name: $key,
            value: $cookieValue,
            minutes: 60 * 24 * 30,
            path: '/',
            domain: null,
            secure: false,
            httpOnly: false,
            sameSite: 'Lax'
        );

        cookie()->queue($cookie);
    }

    return [
        'value' => $cookieValue,
        'cookie' => $cookie,
    ];
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

function getDiscountAmount($discount, $subtotal)
{
    if (!is_object($discount)) {
        return 0;
    }
    if ($discount->type == \App\Enums\DiscountCode\TypeEnum::PERCENTAGE)
    {
        return $subtotal * $discount->value;

    }
    return  $discount->value;

}


function getTotalPrice($discount, $subtotal): float|int
{
    $discountAmount = getDiscountAmount($discount, $subtotal);
    $tax = setting('tax');
    $delivery = setting('delivery');
    return $subtotal - $discountAmount + ($tax * $subtotal) + $delivery;
}

function getPriceAfterTax($tax, $subtotal): float|int
{
    return number_format($tax * $subtotal, 2, '.', '')
}

function setting(string $key, $default = null)
{
    return app(SettingRepository::class)->get($key, $default);
}

function commentableModelClass(string $type): ?string
{
    return match ($type) {
        'design' => Design::class,
        'template' => Template::class,
        default => null,
    };
}
