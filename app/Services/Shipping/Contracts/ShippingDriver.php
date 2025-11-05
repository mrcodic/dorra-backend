<?php
namespace App\Services\Shipping\Contracts;
interface ShippingDriver
{
    public function getRateQuote($rateQuoteDTO,$orderType);
    public function createShipment($addressDTO, $orderId);
    public function requestPickup($trackingNumbers);

    public function track($trackingNumber);

    public function cancel(string $shipmentId);
}
