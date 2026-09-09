<?php

namespace App\Repositories\Implementations;

use App\Models\Bundle;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\BundleRepositoryInterface;

class BundleRepository extends BaseRepository implements BundleRepositoryInterface
{
    public function __construct(Bundle $model)
    {
        parent::__construct($model);
    }
}
