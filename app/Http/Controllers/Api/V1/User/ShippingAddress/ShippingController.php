<?php

namespace App\Http\Controllers\Api\V1\User\ShippingAddress;

use App\Http\Controllers\Controller;
use App\Services\Shipping\ShippingManger;
use Illuminate\Support\Facades\Response;


class ShippingController extends Controller
{
public function __construct(public ShippingManger $shippingManger){}

    public function governorates()
    {
      return  Response::api(data:
            $this->shippingManger->driver('shipblu')->governorates()
        );
    }

    public function cities($governorateId)
    {
        return  Response::api(data:
            $this->shippingManger->driver('shipblu')->cities($governorateId)
        );
    }

    public function zones($cityId)
    {
        return  Response::api(data:
            $this->shippingManger->driver('shipblu')->zones($cityId)
        );
    }

}
