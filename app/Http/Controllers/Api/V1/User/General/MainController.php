<?php

namespace App\Http\Controllers\Api\V1\User\General;


use App\Enums\HttpEnum;
use App\Http\Controllers\Controller;
use App\Models\CountryCode;
use App\Services\CategoryService;
use App\Services\ReviewService;
use App\Http\Resources\{CategoryResource, CountryCodeResource, CountryResource, StateResource};
use App\Repositories\Interfaces\{CategoryRepositoryInterface, CountryRepositoryInterface, StateRepositoryInterface};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class MainController extends Controller
{
    public function __construct(
        public CountryRepositoryInterface  $countryRepository,
        public StateRepositoryInterface    $stateRepository,
        public CategoryService $categoryService,
    )
    {}

    public function addMedia(Request $request, $id)
    {
        $model = ($request->resource)::find($id);
        $media= addMediaToResource($request->allFiles(), $model,clearExisting: true);
        return Response::api(data: $media);
    }

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

    public function subCategories()
    {
        return Response::api(data: CategoryResource::collection($this->categoryService->getSubCategories()));
    }

    public function adminCheck()
    {
        if (auth()->check()) {
            return response()->json(['message' => 'authenticated.'], 200);
        }
    }


}
