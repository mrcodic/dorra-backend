<?php

namespace App\Repositories\Implementations;

use App\Models\Mockup;
use App\Repositories\Base\BaseRepository;

use App\Repositories\Interfaces\MockupRepositoryInterface;


class MockupRepository extends BaseRepository implements MockupRepositoryInterface
{
    public function __construct(Mockup $mockup)
    {
        parent::__construct($mockup);
    }
}
