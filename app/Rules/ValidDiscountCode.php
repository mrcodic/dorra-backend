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
    public function __construct(public ?Product $product = null, public ?Category $category = null , public ?Cart $cart = null)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $code = DiscountCode::whereCode($value)->first();
        if (!$code) {
            $fail('Discount code does not exist.');
        }
        if ($this->cart?->price < $code?->value) {
            $fail('Discount code is not valid for this product.');
        }
        
        if ($code->expired_at && $code->expired_at <= now()) {
            $fail('Discount code has expired.');
            return;
        }

        if ($code->max_usage && $code->used >= $code->max_usage) {
            $fail('This discount code is no longer valid.');
            return;
        }

        if (!$code->products->contains($this->product) && !$code->categories->contains($this->category) && $code->scope != ScopeEnum::GENERAL) {
            $fail('This discount code is not valid for the selected product or category.');
        }

    }
}
