<?php

namespace App\Repositories\Implementations;

use App\Models\Carousel;
use App\Repositories\{Base\BaseRepository, Interfaces\CarouselRepositoryInterface,};

class CarouselRepository extends BaseRepository implements CarouselRepositoryInterface
{
    public function __construct(Carousel $carousel)
    {
        parent::__construct($carousel);
    }

}
