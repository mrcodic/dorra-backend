<?php

namespace App\DTOs\Shipping;

use App\Models\Order;
use App\Models\ShippingLocationMapping;
use App\Models\Zone;
use InvalidArgumentException;

class AddressDTO
{
    public function __construct(public Order $order) {}

    // keep your current factory name/signature
    public static function fromArray(Order $order): self
    {
        return new self(order: $order);
    }

    public function toShipBluPayload(): array
    {
        $order = $this->order;
        $addr  = $order->orderAddress;            // adjust if your relation name differs
        $ship  = $addr->shippingAddress;          // adjust if your relation name differs

        // Resolve provider zone id; fail fast if not mapped
        $zoneExternal = $this->providerZoneId((int) $ship->zone_id, 'shipblu');
        if ($zoneExternal === null) {
            throw new InvalidArgumentException('ShipBlu zone mapping not found for zone_id '.$ship->zone_id);
        }

        // Map your own sizing -> ShipBlu size code (set your real logic here)
        $packageSize = (int) ($order->package_size ?? 1);

        return [
            'customer' => [
                'full_name' => trim(($addr->first_name ?? '').' '.($addr->last_name ?? '')),
                'email'     => (string) ($addr->email ?? ''),
                'phone'     => (string) ($addr->phone ?? ''),
                'address'   => [
                    'line_1' => (string) ($ship->line1 ?? $ship->line ?? ''), // fallback if your column is "line"
                    'line_2' => (string) ($ship->line2 ?? ''),                // can be empty string
                    'zone'   => (int) $zoneExternal,
                ],
            ],
            // IMPORTANT: packages must be TOP-LEVEL
            'packages' => [
                ['package_size' => $packageSize],
            ],
        ];
    }

    private function providerZoneId(int $zoneId, string $provider): ?string
    {
        return ShippingLocationMapping::query()
            ->where('provider', $provider)
            ->whereMorphedTo('locatable', Zone::class)
            ->where('locatable_id', $zoneId)
            ->value('external_id');
    }
}
