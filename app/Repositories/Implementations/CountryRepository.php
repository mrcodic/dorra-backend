<?php

namespace App\Repositories\Implementations;

use App\Models\Country;
use App\Repositories\{Base\BaseRepository,
    Interfaces\CountryRepositoryInterface,
};

class CountryRepository extends BaseRepository implements CountryRepositoryInterface
{
    public function __construct(Country $country)
    {
        parent::__construct($country);
    }

}
