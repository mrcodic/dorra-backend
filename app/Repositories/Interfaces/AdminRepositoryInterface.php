<?php
namespace App\Repositories\Interfaces;
 use App\Models\Admin;
 use App\Models\User;
 use App\Repositories\Base\BaseRepositoryInterface;

 interface AdminRepositoryInterface extends BaseRepositoryInterface
 {
     public function findByEmail(string $email) : ?Admin;

 }
