<?php

namespace App\Repositories\Implementations;

use App\Models\Admin;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\AdminRepositoryInterface;

class AdminRepository extends BaseRepository implements AdminRepositoryInterface
{
    public function __construct(Admin $admin)
    {
        parent::__construct($admin);
    }
    public function findByEmail(string $email): ?Admin
    {
        return Admin::firstWhere('email', $email);
    }
}
