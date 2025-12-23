<?php

namespace App\Repositories\Implementations;

use App\Models\Font;
use App\Repositories\{Base\BaseRepository, Interfaces\FontRepositoryInterface,};

class FontRepository extends BaseRepository implements FontRepositoryInterface
{
    public function __construct(Font $font)
    {
        parent::__construct($font);
    }

}
