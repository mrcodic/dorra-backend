<?php

namespace App\Rules;

use App\Enums\DiscountCode\TypeEnum;
use App\Models\Cart;
use App\Models\Category;
use App\Models\DiscountCode;
use App\Models\Product;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidDiscountCode implements ValidationRule
{
    public function __construct(
        public Product|Category|null $cartable = null,
        public ?Cart $cart = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $code = DiscountCode::where(function ($query) use ($value) {
            $query->where('code', $value)
                ->orWhere('id', $value);
        })->first();

        if (!$code) {
            $fail(__('validation.discount_code_not_exist'));
            return;
        }

        if (
            $code->type === TypeEnum::FIXED &&
            $this->cart?->price < $code->getRawOriginal('value')
        ) {
            $fail(__('validation.cart_total_less_than_discount'));
            return;
        }

        if ($code->expired_at && $code->expired_at <= now()) {
            $fail(__('validation.discount_code_expired'));
            return;
        }

        if ($code->max_usage && $code->used >= $code->max_usage) {
            $fail(__('validation.discount_code_not_valid'));
            return;
        }

        if (
            $code->show_for_new_registered_users &&
            !auth()->guard('sanctum')->check()
        ) {
            $fail(__('validation.login_required_for_promo_code'));
            return;
        }

        if (
            $code->show_for_new_registered_users &&
            auth('sanctum')->user()?->created_at->addMonth()->isPast()
        ) {
            $fail(__('validation.promo_code_only_new_users'));
            return;
        }

        if (
            $code->show_for_new_registered_users &&
            auth('sanctum')->user()?->discount_code_id == null
        ) {
            $fail(__('validation.promo_code_already_used'));
            return;
        }

        if (
            $code->show_for_new_registered_users &&
            auth('sanctum')->user()?->discount_code_id !== $code->id
        ) {
            $fail(__('validation.promo_code_not_assigned'));
            return;
        }

//        if (
//            !$code?->products->contains($this->cartable) &&
//            !$code?->categories->contains($this->cartable) &&
//            $code?->scope != ScopeEnum::GENERAL
//        ) {
//            $fail(__('validation.discount_code_invalid_for_selection'));
//        }
    }
}
