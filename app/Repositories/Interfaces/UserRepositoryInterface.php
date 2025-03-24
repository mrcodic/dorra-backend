<?php
namespace App\Repositories\Interfaces;
 use App\Models\User;
 use App\Repositories\Base\BaseRepositoryInterface;

 interface UserRepositoryInterface extends BaseRepositoryInterface
 {
     public function findByEmail(string $email) : ?User;

 }
