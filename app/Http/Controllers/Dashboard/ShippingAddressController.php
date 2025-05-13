<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Services\ShippingAddressService;
use App\Http\Requests\User\ShippingAddress\{StoreShippingAddressRequest, UpdateShippingAddressRequest};

class ShippingAddressController extends DashboardController
{
    public function __construct(public ShippingAddressService $shippingAddress)
    {
        parent::__construct($shippingAddress);
        $this->storeRequestClass = new StoreShippingAddressRequest();
        $this->updateRequestClass = new UpdateShippingAddressRequest();
    }



}
