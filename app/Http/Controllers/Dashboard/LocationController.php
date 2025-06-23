<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Services\LocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Base\DashboardController;

use App\Repositories\Interfaces\TagRepositoryInterface;

use App\Repositories\Interfaces\CountryRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Http\Requests\Location\{StoreLocationRequest, UpdateLocationRequest};


class LocationController extends DashboardController
{
    public function __construct(public LocationService $locationService,
        public CategoryRepositoryInterface $categoryRepository,
        public TagRepositoryInterface $tagRepository,
        public CountryRepositoryInterface $countryRepository,
        )
    {
        parent::__construct($locationService);
        $this->storeRequestClass = new StoreLocationRequest();
        $this->updateRequestClass = new UpdateLocationRequest();
        $this->indexView = 'logistics.location';
        $this->usePagination = true;
        $this->resourceTable = 'locations';


         $this->assoiciatedData = [
            'index' => [
                'countries' => $this->countryRepository->query(['id', 'name'])->get(),
            ],
            ];
    }

    public function getData(): JsonResponse
    {
        return $this->locationService->getData();
    }
    public function dashboard()
    {
        return view("dashboard.logistics.dashboard");
    }



public function store(Request $request)
{
    $validatedData = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'country_id' => ['required', 'exists:countries,id'], // Added this validation
        'state_id' => ['required', 'exists:states,id'],
        'address_line' => ['required', 'string', 'max:255'],
        'link' => ['required', 'string', 'max:255'],
        'days' => ['required', 'array', 'min:1'],
        'days.*' => ['string'],
        'available_time' => ['required', 'regex:/^\d{2}:\d{2}\s?-\s?\d{2}:\d{2}$/'],
    ]);

    $location = $this->locationService->storeResource($validatedData);
    
    return Response::api(
        message: 'Location created successfully!',
        data: [
            'location' => $location->id,
            'name' => $location->name,
            'redirect_url' => route('logistics.index', $location->id)
        ]
    );
}

}
