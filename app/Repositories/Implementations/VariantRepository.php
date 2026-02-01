<?php

namespace App\Repositories\Implementations;

use App\Models\Variant;
use App\Repositories\{Base\BaseRepository,
    Interfaces\VariantRepositoryInterface};

class VariantRepository extends BaseRepository implements VariantRepositoryInterface
{
    public function __construct(Variant $variant)
    {
        parent::__construct($variant);
    }

}
