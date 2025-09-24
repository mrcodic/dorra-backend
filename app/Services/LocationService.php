<?php

namespace App\Services;

use App\Enums\Location\DayEnum;
use Illuminate\Support\Facades\Http;
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
                    $locale = app()->getLocale();
                    $search = request('search_value');
                    $query->where("name->{$locale}", 'LIKE', "%{$search}%");
                } else {
                    $query->whereRaw('1 = 0');
                }
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
        // days handling (unchanged)
        if (isset($validatedData['days']) && is_array($validatedData['days'])) {
            $dayValues = [];
            foreach ($validatedData['days'] as $day) {
                if (is_string($day)) {
                    $enumCase = collect(DayEnum::cases())->firstWhere('name', strtoupper($day));
                    if ($enumCase) $dayValues[] = $enumCase->value;
                } elseif (is_int($day)) {
                    $enumCase = DayEnum::tryFrom($day);
                    if ($enumCase) $dayValues[] = $day;
                }
            }
            $validatedData['days'] = json_encode($dayValues);
        }

        // If link provided, extract coordinates
        if (!empty($validatedData['link'])) {
            $coordinates = $this->extractCoordinatesFromLink($validatedData['link']);
            if ($coordinates) {
                $validatedData['latitude']  = $coordinates['latitude'];
                $validatedData['longitude'] = $coordinates['longitude'];

                // Only auto-fill if not manually provided
                $needsCountry = empty($validatedData['country']);
                $needsState   = empty($validatedData['state']);

                if ($needsCountry || $needsState) {
                    $lang = app()->getLocale(); // 'ar' or 'en'
                    $geo  = $this->reverseGeocode($coordinates['latitude'], $coordinates['longitude'], $lang);

                    if ($needsCountry && $geo['country']) {
                        $validatedData['country'] = $geo['country'];
                        // If you also added country_code:
                        // $validatedData['country_code'] = $geo['country_code'];
                    }
                    if ($needsState && $geo['state']) {
                        $validatedData['state'] = $geo['state'];
                    }
                }
            }
        }

        // Create with country/state strings
        $location = $this->repository->create($validatedData);

        // Relations (unchanged)
        if (!empty($relationsToStore)) {
            foreach ($relationsToStore as $relation => $data) {
                if (method_exists($location, $relation)) {
                    $location->{$relation}()->attach($data);
                }
            }
        }

        // Load (you can drop 'state' relation if you no longer use FK)
        if (!empty($relationsToLoad)) {
            $location->load($relationsToLoad);
        } else {
            // If you removed state FK, adjust/remove these:
            // $location->load($this->relations);
        }

        return $location;
    }


    private function reverseGeocode(float $lat, float $lng, string $lang = 'en'): array
    {
        $key = config('services.google_maps.key');
        if (!$key) {
            return ['country' => null, 'state' => null, 'country_code' => null];
        }

        $res = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'latlng'   => "{$lat},{$lng}",
            'key'      => $key,
            'language' => $lang,
        ])->json();
dd($res);
        if (($res['status'] ?? '') !== 'OK') {
            return ['country' => null, 'state' => null, 'country_code' => null];
        }

        $components = $res['results'][0]['address_components'] ?? [];
        $pick = function (string $type) use ($components) {
            foreach ($components as $c) {
                if (in_array($type, $c['types'], true)) {
                    return $c;
                }
            }
            return null;
        };

        $country = $pick('country');
        $state   = $pick('administrative_area_level_1');

        return [
            'country'      => $country['long_name']  ?? null,
            'country_code' => $country['short_name'] ?? null,
            'state'        => $state['long_name']    ?? null,
        ];
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


    public function search($request)
    {
        $queryString = $request->search;
        return $this->repository->query()
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
            })
            ->get();

    }
}


