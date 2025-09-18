<?php

namespace App\Repositories\Implementations;

use App\Models\Flag;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\FlagRepositoryInterface;

class FlagRepository extends BaseRepository implements FlagRepositoryInterface
{
    public function __construct(Flag $flag)
    {
        parent::__construct($flag);
    }

}
