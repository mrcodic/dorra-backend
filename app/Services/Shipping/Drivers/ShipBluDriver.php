<?php

namespace App\Services\Shipping\Drivers;

use App\DTOs\Shipping\AddressDTO;
use App\Repositories\Interfaces\ShipmentRepositoryInterface;
use App\Services\Shipping\Contracts\LocationsProvider;
use App\Services\Shipping\Contracts\ShippingDriver;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class ShipBluDriver implements ShippingDriver, LocationsProvider
{
    public function __construct(
        private string $apiKey,
        private string $baseUrl,
        private ShipmentRepositoryInterface $shipmentRepository,
    )
    {
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function governorates()
    {

        return Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Api-Key ' . "$this->apiKey",
        ])->get($this->baseUrl . 'api/v1/governorates')
            ->throw()
            ->json();
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function cities($governorateId)
    {
        return Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Api-Key ' . "$this->apiKey",
        ])->get($this->baseUrl . "api/v1/governorates/$governorateId/cities")
            ->throw()
            ->json();
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function zones($cityId)
    {
        return Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Api-Key ' . "$this->apiKey",
        ])->get($this->baseUrl . "api/v1/cities/$cityId/zones")
            ->throw()
            ->json();
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function createShipment($addressDTO, $orderId)
    {
        $result = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Api-Key ' . "$this->apiKey",
            ])->post($this->baseUrl . "api/v1/delivery-orders/", $addressDTO->toShipBluPayload())
            ->throw()
            ->json();
        $this->shipmentRepository->create([
            'provider' => config('shipping.default' ?? 'shipblu'),
            'provider_order_id' => $result['id'],
            'tracking_number' => $result['tracking_number'],
            'status' => $result['status'],
            'order_id' => $orderId,
            'meta' => $result['metadata'],
        ]);
    }

    public function track($trackingNumber)
    {
        // TODO: Implement track() method.
    }

    public function cancel(string $shipmentId)
    {
        // TODO: Implement cancel() method.
    }

    public function requestPickup($payload)
    {
        // TODO: Implement requestPickup() method.
    }
}
