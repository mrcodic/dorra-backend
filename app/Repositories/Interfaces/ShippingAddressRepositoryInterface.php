<?php
namespace App\Repositories\Interfaces;
 use App\Repositories\Base\BaseRepositoryInterface;

 interface ShippingAddressRepositoryInterface extends BaseRepositoryInterface
 {
     public function getShippingAddressesForUser($user);
 }
