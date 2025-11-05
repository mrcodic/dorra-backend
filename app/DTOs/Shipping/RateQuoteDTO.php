<?php

namespace App\DTOs\Shipping;

use App\Models\Cart;
use App\Models\Country;
use App\Models\ShippingLocationMapping;


class RateQuoteDTO
{
    public function __construct(public Cart $cart, public bool $cod, public int $countryId)
    {
    }

    public static function fromArray(Cart $cart,  bool $cod, int $countryId): self
    {
        return new self($cart, $cod, $countryId);
    }

    public function toShipBluPayload(): array
    {
        return [
            'to_governorate' =>$this->providerGovernorateId($this->countryId,'shipblu'),
            'cash_amount' => $this->cod ? $this->cart->price : 0,
            'declared_value' => $this->cart->price,
            'is_customer_allowed_to_open_packages' => false,
            'packages' => [
                $this->cart->items->count(),
            ],
        ];
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
