<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Carousel\UpdateCarouselRequest;
use App\Http\Requests\Template\UpdateTemplateRequest;
use App\Repositories\Interfaces\CarouselRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\SettingRepositoryInterface;
use Illuminate\Support\Facades\Response;

class SettingController extends Controller
{


    public function __construct()
    {
    }

    public function details()
    {
        return view("dashboard.settings.details");
    }

    public function notifications()
    {
        return view("dashboard.settings.notifications");
    }

    public function payments()
    {
        return view("dashboard.settings.payments");
    }

    public function website(CategoryRepositoryInterface $repository,
                            CarouselRepositoryInterface $carouselRepository,
                            ProductRepositoryInterface  $productRepository,
                            SettingRepositoryInterface  $settingRepository,)
    {
        $categories = $repository->query()->isLanding()->get();
        $carousels = $carouselRepository->all();
        $products = $productRepository->all(columns: ['id', 'name']);
        return view("dashboard.settings.website", get_defined_vars());
    }

    public function carouselUpdate($id, UpdateCarouselRequest $request, CarouselRepositoryInterface $carouselRepository)
    {
        $model = $carouselRepository->update($id, $request->validated());
        if (request()->allFiles()) {
            handleMediaUploads(request()->allFiles(), $model, clearExisting: true);
        }
        return Response::api();

    }
}


