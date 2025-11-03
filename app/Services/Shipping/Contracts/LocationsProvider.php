<?php
namespace App\Services\Shipping\Contracts;
interface LocationsProvider
{
    public function governorates();
    public function cities($governorateId);
    public function zones($cityId);
}
