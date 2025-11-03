<?php

return [
  'default' => env('SHIPPING_DRIVER', 'shipblu'),
    'drivers' => [
        'shipblu' => [
            'base_url' => env('SHIPBLU_BASE_URL', 'https://api.shipblu.com/api/v1'),
            'api_key'    => env('SHIPBLU_API_KEY'),
        ],
        ],
];
