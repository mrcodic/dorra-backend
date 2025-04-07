<?php

namespace App\Http\Controllers\Api\V1\User\General;


use App\Http\Controllers\Controller;
use App\Models\CountryCode;
use App\Http\Resources\{CountryCodeResource, CountryResource, StateResource};
use App\Repositories\Interfaces\{CountryRepositoryInterface, StateRepositoryInterface};
use Illuminate\Support\Facades\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;



class MainController extends Controller
{
    public function __construct(public CountryRepositoryInterface $countryRepository, public StateRepositoryInterface $stateRepository){}

    public function removeMedia(Media $media)
    {
        deleteMediaById($media->uuid);
        return Response::api();
    }

    public function countries()
    {
        return Response::api(data: CountryResource::collection($this->countryRepository->all()));
    }

    public function states()
    {
        return Response::api(data: StateResource::collection($this->stateRepository->getWithFilters()));
    }

    public function countryCodes()
    {
        return Response::api(data: CountryCodeResource::collection(CountryCode::all()));
    }
}
