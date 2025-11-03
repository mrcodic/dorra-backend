<?php

namespace App\Services\Shipping\Drivers;

use App\Services\Shipping\Contracts\LocationsProvider;
use App\Services\Shipping\Contracts\ShippingDriver;
use Illuminate\Support\Facades\Http;

class ShipBluDriver implements ShippingDriver, LocationsProvider
{
    public function __construct(
        private string $apiKey,
        private string $baseUrl
    ){}

    public function governorates()
    {

     return Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Api-Key '."$this->apiKey",
        ])->get($this->baseUrl.'api/v1/governorates')
            ->json();
    }

    public function cities($governorateId)
    {
        return Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Api-Key '."$this->apiKey",
        ])->get($this->baseUrl."api/v1/governorates/$governorateId/cities")
            ->json();
    }

    public function zones($cityId)
    {
        return Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Api-Key '."$this->apiKey",
        ])->get($this->baseUrl."api/v1/cities/$cityId/zones")
            ->json();
    }

    public function createShipment($order)
    {
        // TODO: Implement createShipment() method.
    }

    public function track($trackingNumber)
    {
        // TODO: Implement track() method.
    }

    public function cancel(string $shipmentId): bool
    {
        // TODO: Implement cancel() method.
    }
}
