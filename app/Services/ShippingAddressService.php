<?php

namespace App\Services;

use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\ShippingAddressRepositoryInterface;

class ShippingAddressService extends BaseService
{

    public function __construct(ShippingAddressRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }

    public function getUserShippingAddresses($user)
    {
        return $this->repository->getShippingAddressesForUser($user);
    }

}
