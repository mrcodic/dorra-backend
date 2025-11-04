<?php

namespace App\Repositories\Implementations;

use App\Models\Shipment;
use App\Repositories\{Base\BaseRepository, Interfaces\ShipmentRepositoryInterface};


class ShipmentRepository extends BaseRepository implements ShipmentRepositoryInterface
{
    public function __construct(Shipment $shipment)
    {
        parent::__construct($shipment);
    }



}
