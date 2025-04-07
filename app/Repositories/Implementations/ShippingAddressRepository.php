<?php

namespace App\Repositories\Implementations;

use App\Models\Country;
use App\Models\ShippingAddress;
use App\Repositories\{Base\BaseRepository,
    Interfaces\CountryRepositoryInterface,
    Interfaces\ShippingAddressRepositoryInterface};

class ShippingAddressRepository extends BaseRepository implements ShippingAddressRepositoryInterface
{
    public function __construct(ShippingAddress $shippingAddress)
    {
        parent::__construct($shippingAddress);
    }
    public function getShippingAddressesForUser($user)
    {
        return $user->shippingAddresses->load('state');
    }


}
