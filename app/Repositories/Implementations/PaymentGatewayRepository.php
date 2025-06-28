<?php

namespace App\Repositories\Implementations;

use App\Models\PaymentGateway;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\PaymentGatewayRepositoryInterface;

class PaymentGatewayRepository extends BaseRepository implements PaymentGatewayRepositoryInterface
{
    public function __construct(PaymentGateway $gateway)
    {
        parent::__construct($gateway);
    }
}
