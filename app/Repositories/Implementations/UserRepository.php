<?php

namespace App\Repositories\Implementations;

use App\Models\User;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $user)
    {
        parent::__construct($user);
    }


    public function findByEmail(string $email): ?User
    {
        return User::firstWhere('email', $email);
    }
}
