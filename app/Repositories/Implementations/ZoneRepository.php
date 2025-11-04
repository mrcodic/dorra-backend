<?php

namespace App\Repositories\Implementations;

use App\Models\State;
use App\Models\Zone;
use App\Repositories\{Base\BaseRepository, Interfaces\ZoneRepositoryInterface};
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ZoneRepository extends BaseRepository implements ZoneRepositoryInterface
{
    public function __construct(Zone $zone)
    {
        parent::__construct($zone);
    }

    public function getWithFilters(): Collection
    {
        return QueryBuilder::for(Zone::class)
            ->allowedFilters([
                AllowedFilter::exact('state_id'),
            ])
            ->get();
    }

}
