<?php

namespace App\Http\Controllers\Api\V1\User\CMS;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CMS\CarouselResource;
use App\Models\Carousel;
use App\Services\CategoryService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Response;


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

}
