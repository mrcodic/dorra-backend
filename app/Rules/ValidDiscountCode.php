<?php

namespace App\Rules;

use App\Enums\DiscountCode\ScopeEnum;
use App\Models\Cart;
use App\Models\Category;
use App\Models\DiscountCode;
use App\Models\Product;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidDiscountCode implements ValidationRule
{
    public function __construct(public Product|Category|null $cartable = null, public ?Cart $cart = null)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $code = DiscountCode::whereCode($value)->first();
        if (!$code) {
            $fail('Discount code does not exist.');
            return;
        }
        if ($this->cart?->price < $code?->value) {
            $fail('Discount code is not valid for this product.');
        }

        if ($code?->expired_at && $code?->expired_at <= now()) {
            $fail('Discount code has expired.');
            return;
        }

        if ($code?->max_usage && $code?->used >= $code?->max_usage) {
            $fail('This discount code is no longer valid.');
            return;
        }
        if ($code?->show_for_new_registered_users && !auth()->guard('sanctum')->check()) {
            $fail('You must be logged in to use a promotional code.');
            return;
        }
        if ($code?->show_for_new_registered_users && auth('sanctum')->user()?->created_at->addMonth()->isPast()) {
            $fail('This promotional code is only available for new users within their first month of registration.');
            return;
        }
        if ($code?->show_for_new_registered_users && auth('sanctum')->user()?->discount_code_id == null) {
            $fail('This promotional code has already been used in last order.');
            return;
        }
        if ($code?->show_for_new_registered_users && auth('sanctum')->user()?->discount_code_id !== $code?->id) {
            $fail('This promotional code is not assigned to your account.');
            return;
        }

        if (!$code?->products->contains($this->cartable) && !$code?->categories->contains($this->cartable) && $code?->scope != ScopeEnum::GENERAL) {
            $fail('This discount code is not valid for the selected product or category.');
        }

    }
}
