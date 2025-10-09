<?php

namespace App\Repositories\Implementations;

use App\Models\Cart;
use App\Models\Type;
use App\Repositories\{Base\BaseRepository, Interfaces\TypeRepositoryInterface,};

class TypeRepository extends BaseRepository implements TypeRepositoryInterface
{
    public function __construct(Type $type)
    {
        parent::__construct($type);
    }

}
