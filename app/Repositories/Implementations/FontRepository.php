<?php

namespace App\Repositories\Implementations;

use App\Models\Carousel;
use App\Repositories\{Base\BaseRepository, Interfaces\FontRepositoryInterface,};

class FontRepository extends BaseRepository implements FontRepositoryInterface
{
    public function __construct(Carousel $carousel)
    {
        parent::__construct($carousel);
    }

}
