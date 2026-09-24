<?php

namespace App\DTOs\Shipping;

use App\Models\Cart;
use App\Models\Country;
use App\Models\ShippingLocationMapping;

class RateQuoteDTO
{
    public function __construct(
        public Cart $cart,
        public bool $cod,
        public int $countryId
    ) {}

    public static function fromArray(Cart $cart, bool $cod, int $countryId): self
    {
        return new self($cart, $cod, $countryId);
    }

    public function toShipBluPayload(): array
    {
        $cartAmount = $this->cartAmountAfterDiscounts();

        return [
            'to_governorate' => $this->providerGovernorateId($this->countryId, 'shipblu'),
            'cash_amount' => $this->cod ? $cartAmount : 0,
            'declared_value' => $cartAmount,
            'is_customer_allowed_to_open_packages' => false,
            'packages' => [
                $this->cart->items_count ?? $this->cart->items?->count() ?? 1,
            ],
        ];
    }
    private function cartHasDiscount(): bool
    {
        if ((float) ($this->cart->discount_amount ?? 0) > 0) {
            return true;
        }

        if (! empty($this->cart->discount_code_id)) {
            return true;
        }

        if ($this->cart->relationLoaded('items')) {
            return $this->cart->items->contains(function ($item) {
                return (float) ($item->discount_amount ?? 0) > 0
                    || ! empty($item->discount_code_id);
            });
        }

        return false;
    }
    private function cartAmountAfterDiscounts(): float
    {
        $cartPrice = (float) ($this->cart->price ?? 0);

        if (! $this->cartHasDiscount()) {
            return round(max(0, $cartPrice), 2);
        }

        if ($this->cart->relationLoaded('items')) {
            $itemsTotal = $this->cart->items->sum(function ($item) {
                $subTotal = (float) ($item->sub_total ?? 0);
                $itemDiscount = (float) ($item->discount_amount ?? 0);

                return max(0, $subTotal - $itemDiscount);
            });

            $cartDiscount = (float) ($this->cart->discount_amount ?? 0);

            return round(max(0, $itemsTotal - $cartDiscount), 2);
        }
        $cartDiscount = (float) ($this->cart->discount_amount ?? 0);

        return round(max(0, $cartPrice - $cartDiscount), 2);
    }

    private function providerGovernorateId(int $governorateId, string $provider): ?string
    {
        return ShippingLocationMapping::query()
            ->where('provider', $provider)
            ->whereMorphedTo('locatable', Country::class)
            ->where('locatable_id', $governorateId)
            ->value('external_id');
    }
}
