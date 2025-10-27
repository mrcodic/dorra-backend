<?php

namespace App\Services;

use App\Enums\Location\DayEnum;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Location\StoreLocationRequest;
use App\Repositories\Interfaces\LocationRepositoryInterface;
use function Laravel\Prompts\search;


class LocationService extends BaseService
{
    protected array $relations;

    public function __construct(
        LocationRepositoryInterface $repository,

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
                if (hasMeaningfulSearch(request('search_value'))) {
                    $search = request('search_value');
                    $query->where("name", 'LIKE', "%{$search}%");
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->latest();

        return DataTables::of($locations)
            ->addColumn('name', function ($location) {
                return $location->name ?? '-';
            })
            ->addColumn('action', function () {
                return [
                    'can_edit' => (bool)auth()->user()->can('locations_update'),
                    'can_delete' => (bool)auth()->user()->can('locations_delete'),
                ];
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
                'latitude' => (float)$matches[1],
                'longitude' => (float)$matches[2]
            ];
        } elseif (preg_match('/q=(-?\d+\.?\d*),(-?\d+\.?\d*)/', $link, $matches)) {
            $coordinates = [
                'latitude' => (float)$matches[1],
                'longitude' => (float)$matches[2]
            ];
        } elseif (preg_match('/ll=(-?\d+\.?\d*),(-?\d+\.?\d*)/', $link, $matches)) {
            $coordinates = [
                'latitude' => (float)$matches[1],
                'longitude' => (float)$matches[2]
            ];
        } elseif (preg_match('/!3d(-?\d+\.?\d*)!4d(-?\d+\.?\d*)/', $link, $matches)) {
            $coordinates = [
                'latitude' => (float)$matches[1],
                'longitude' => (float)$matches[2]
            ];
        } elseif (preg_match('/maps\.apple\.com.*ll=(-?\d+\.?\d*),(-?\d+\.?\d*)/', $link, $matches)) {
            $coordinates = [
                'latitude' => (float)$matches[1],
                'longitude' => (float)$matches[2]
            ];
        } elseif (preg_match('/openstreetmap\.org.*#map=\d+\/(-?\d+\.?\d*)\/(-?\d+\.?\d*)/', $link, $matches)) {
            $coordinates = [
                'latitude' => (float)$matches[1],
                'longitude' => (float)$matches[2]
            ];
        } elseif (preg_match('/(-?\d+\.?\d*),\s*(-?\d+\.?\d*)/', $link, $matches)) {
            $coordinates = [
                'latitude' => (float)$matches[1],
                'longitude' => (float)$matches[2]
            ];
        }

        return $coordinates;
    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        if (isset($validatedData['link'])) {
            $coordinates = $this->extractCoordinatesFromLink($validatedData['link']);
            if ($coordinates) {
                $validatedData['latitude'] = $coordinates['latitude'];
                $validatedData['longitude'] = $coordinates['longitude'];
            }
        }
        return $this->repository->update($validatedData, $id);

    }

    public function search($request)
    {
        $queryString = (string)$request->search;
        $lat = $request->float('latitude');
        $lng = $request->float('longitude');
        $radiusKm = (int)$request->input('radius', 10); // default 10km

        // base query
        $q = $this->repository->query()
            ->when($request->filled('search'), function ($query) use ($queryString) {
                $query->where(function ($q) use ($queryString) {
                    $q->where('name', 'like', '%' . $queryString . '%')
                        ->orWhere('address_line', 'like', '%' . $queryString . '%')
                        ->orWhereHas('state', function ($stateQuery) use ($queryString) {
                            $stateQuery->where('name', 'like', '%' . $queryString . '%')
                                ->orWhereHas('country', function ($countryQuery) use ($queryString) {
                                    $countryQuery->where('name', 'like', '%' . $queryString . '%');
                                });
                        });
                });
            });

        // If lat/lng provided, compute distance and filter/order by nearest
        if ($request->filled('latitude') && $request->filled('longitude')) {
            // Haversine (Earth radius 6371 km). LEAST(1, ...) guards tiny FP >1 errors in acos()
            $haversine = '6371 * acos(LEAST(1,
            cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?))
          + sin(radians(?)) * sin(radians(latitude))
        ))';

            // Optional bounding box optimization to speed up (rough pre-filter)
            $latDelta = $radiusKm / 111.32; // ~km per degree latitude
            $lngDelta = $radiusKm / (111.32 * max(cos(deg2rad(max(min($lat, 89.9), -89.9))), 0.0001));

            $q->whereBetween('latitude', [$lat - $latDelta, $lat + $latDelta])
                ->whereBetween('longitude', [$lng - $lngDelta, $lng + $lngDelta])
                ->select('*')
                ->selectRaw("$haversine AS distance_km", [$lat, $lng, $lat])
                ->when($radiusKm > 0, fn($qq) => $qq->having('distance_km', '<=', $radiusKm))
                ->orderBy('distance_km');
        }

        // Optional: allow limiting results
        if ($request->filled('take')) {
            $q->limit((int)$request->input('take'));
        }

        return $q->get();
    }
}


