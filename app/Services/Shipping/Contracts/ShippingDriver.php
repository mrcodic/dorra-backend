<?php
namespace App\Services\Shipping\Contracts;
interface ShippingDriver
{
    public function createShipment($addressDTO, $orderId);
    public function requestPickup($payload);

    public function track($trackingNumber);

    public function cancel(string $shipmentId);
}
