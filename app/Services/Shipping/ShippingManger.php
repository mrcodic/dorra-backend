<?php

namespace App\Services\Shipping;

use App\Services\Shipping\Drivers\ShipBluDriver;
use Illuminate\Support\Manager;

final class ShippingManger extends Manager
{

    public function getDefaultDriver()
    {
        return $this->config->get('shipping.default', 'shipblu');
    }

    protected function createShipbluDriver(): ShipBluDriver
    {
        $cfg = (array)$this->config->get('shipping.drivers.shipblu', []);

        return new ShipBluDriver(
            apiKey: (string)($cfg['api_key'] ?? $cfg['token'] ?? ''),
            baseUrl: rtrim((string)($cfg['base_url'] ?? 'https://api.shipblu.com/'), '/') . '/',
        );
    }
}
