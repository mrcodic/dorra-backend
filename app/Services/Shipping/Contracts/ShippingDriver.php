<?php
namespace App\Services\Shipping\Contracts;
interface ShippingDriver
{
    public function createShipment($order);

    public function track($trackingNumber);

    public function cancel(string $shipmentId): bool;
}
