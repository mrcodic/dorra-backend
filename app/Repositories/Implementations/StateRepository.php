<?php

namespace App\Repositories\Implementations;

use App\Models\State;
use App\Repositories\{Base\BaseRepository, Interfaces\StateRepositoryInterface};
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StateRepository extends BaseRepository implements StateRepositoryInterface
{
    public function __construct(State $state)
    {
        parent::__construct($state);
    }

    public function getWithFilters(): Collection
    {
        return QueryBuilder::for(State::class)
            ->allowedFilters([
                AllowedFilter::exact('country_id'),
            ])
            ->get();
    }

}
