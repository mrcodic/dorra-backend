<?php

namespace App\Services;

use App\Enums\Location\DayEnum;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Location\StoreLocationRequest;
use App\Repositories\Interfaces\LocationRepositoryInterface;




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

public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
{
    if (isset($validatedData['days']) && is_array($validatedData['days'])) {
        $dayValues = [];

        foreach ($validatedData['days'] as $day) {
            if (is_string($day)) {
                // Convert string day name to enum case
                $enumCase = collect(DayEnum::cases())->firstWhere('name', strtoupper($day));
                if ($enumCase) {
                    $dayValues[] = $enumCase->value;
                }
            } elseif (is_int($day)) {
                // Validate integer is a valid enum value
                $enumCase = DayEnum::tryFrom($day);
                if ($enumCase) {
                    $dayValues[] = $day;
                }
            }
        }

        $validatedData['days'] = json_encode($dayValues);
    }

    if (isset($validatedData['link'])) {
        $coordinates = $this->extractCoordinatesFromLink($validatedData['link']);
        if ($coordinates) {
            $validatedData['latitude'] = $coordinates['latitude'];
            $validatedData['longitude'] = $coordinates['longitude'];
        }
    }

    $location = $this->repository->create($validatedData);

    if (!empty($relationsToStore)) {
        foreach ($relationsToStore as $relation => $data) {
            if (method_exists($location, $relation)) {
                $location->{$relation}()->attach($data);
            }
        }
    }

    if (!empty($relationsToLoad)) {
        $location->load($relationsToLoad);
    } else {
        $location->load($this->relations);
    }

    return $location;
}
    
    /**
     * Extract latitude and longitude from various map link formats
     */
    private function extractCoordinatesFromLink($link)
    {
        $coordinates = null;
        
        if (preg_match('/@(-?\d+\.?\d*),(-?\d+\.?\d*)/', $link, $matches)) {
            $coordinates = [
                'latitude' => (float) $matches[1],
                'longitude' => (float) $matches[2]
            ];
        } elseif (preg_match('/q=(-?\d+\.?\d*),(-?\d+\.?\d*)/', $link, $matches)) {
            $coordinates = [
                'latitude' => (float) $matches[1],
                'longitude' => (float) $matches[2]
            ];
        } elseif (preg_match('/ll=(-?\d+\.?\d*),(-?\d+\.?\d*)/', $link, $matches)) {
            $coordinates = [
                'latitude' => (float) $matches[1],
                'longitude' => (float) $matches[2]
            ];
        } elseif (preg_match('/!3d(-?\d+\.?\d*)!4d(-?\d+\.?\d*)/', $link, $matches)) {
            $coordinates = [
                'latitude' => (float) $matches[1],
                'longitude' => (float) $matches[2]
            ];
        }
        elseif (preg_match('/maps\.apple\.com.*ll=(-?\d+\.?\d*),(-?\d+\.?\d*)/', $link, $matches)) {
            $coordinates = [
                'latitude' => (float) $matches[1],
                'longitude' => (float) $matches[2]
            ];
        }
        elseif (preg_match('/openstreetmap\.org.*#map=\d+\/(-?\d+\.?\d*)\/(-?\d+\.?\d*)/', $link, $matches)) {
            $coordinates = [
                'latitude' => (float) $matches[1],
                'longitude' => (float) $matches[2]
            ];
        }
        elseif (preg_match('/(-?\d+\.?\d*),\s*(-?\d+\.?\d*)/', $link, $matches)) {
            $coordinates = [
                'latitude' => (float) $matches[1],
                'longitude' => (float) $matches[2]
            ];
        }
        
        return $coordinates;
    }
}


