<?php

namespace App\Services;

use App\Repositories\Interfaces\LocationRepositoryInterface;
use Yajra\DataTables\Facades\DataTables;




class LocationService extends BaseService
{
        protected array $relations;
    public function __construct(
        LocationRepositoryInterface         $repository,
        
    )
    {
        $this->relations = [
        'state',
        'state.country',
    ];
        parent::__construct($repository);
    }
  public function getData()
{
    $locations = $this->repository
        ->query()
        ->with($this->relations)
        ->when(request()->filled('search_value'), function ($query) {
            $locale = app()->getLocale();
            $search = request('search_value');
            $query->where("name->{$locale}", 'LIKE', "%{$search}%");
        })
        ->latest();

    return DataTables::of($locations)
        ->addColumn('name', function ($location) {
            return $location->name ?? '-';
        })
        ->addColumn('state', function ($location) {
            return optional($location->state)->name ?? '-';
        })
        ->addColumn('country', function ($location) {
            return optional($location->state?->country)->name ?? '-';
        })
        ->make(true);
}

}
