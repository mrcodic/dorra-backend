<?php

namespace App\Repositories\Implementations;

use App\Models\PaymentMethod;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\PaymentMethodRepositoryInterface;

class PaymentMethodRepository extends BaseRepository implements PaymentMethodRepositoryInterface
{
    public function __construct(PaymentMethod $method)
    {
        parent::__construct($method);
    }
}
