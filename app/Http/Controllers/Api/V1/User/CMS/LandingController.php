<?php

namespace App\Http\Controllers\Api\V1\User\CMS;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CMS\CarouselResource;
use App\Http\Resources\CMS\LandingReviewResource;
use App\Http\Resources\MediaResource;
use App\Models\Carousel;
use App\Repositories\Interfaces\LandingReviewRepositoryInterface;
use App\Services\CategoryService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class LandingController extends Controller
{
    public function __construct(public CategoryService $categoryService)
    {
    }

    public function carousels()
    {
        $carousels = Carousel::with('product')->get();
        return Response::api(data: CarouselResource::collection($carousels));
    }

    public function visibilitySections()
    {
        return Response::api(data: setting(group: "visibility_sections_landing"));

    }

    public function statistics()
    {
        return Response::api(data: setting(group: "statistics_landing"));

    }

    public function partners()
    {
        $partners = Media::query()->whereCollectionName('partners')->get();
        return Response::api(data: MediaResource::collection($partners));
    }

    public function reviewsWithImages(LandingReviewRepositoryInterface $landingReviewRepository)
    {
        $reviewsWithImages = $landingReviewRepository->query()->with('media')->whereType('with_image')->get();
        return Response::api(data: LandingReviewResource::collection($reviewsWithImages));
    }
    public function reviewsWithoutImages(LandingReviewRepositoryInterface $landingReviewRepository)
    {
        $reviewsWithoutImages = $landingReviewRepository->query()->whereType('without_image')->get();
        return Response::api(data: LandingReviewResource::collection($reviewsWithoutImages));
    }

}
