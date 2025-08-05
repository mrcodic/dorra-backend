<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Carousel\UpdateCarouselRequest;
use App\Http\Requests\Template\UpdateTemplateRequest;
use App\Repositories\Interfaces\CarouselRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\SettingRepositoryInterface;
use App\Repositories\Interfaces\TemplateRepositoryInterface;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
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
                            TemplateRepositoryInterface  $templateRepository)
    {
        $categories = $repository->query()->isLanding()->get();
        $carousels = $carouselRepository->all();
        $products = $productRepository->all(columns: ['id', 'name']);
        $templates = $templateRepository->query()->isLanding()->get(['id','name']);
        $allCategories = $repository->query()->get();

        return view("dashboard.settings.website", get_defined_vars());
    }

    public function createOrUpdateCarousel(UpdateCarouselRequest $request, CarouselRepositoryInterface $carouselRepository, $id)
    {
        $validatedData = $request->validated();
        $model = $carouselRepository->query()->
        updateOrCreate(['id' => $id], [
            'title' => [
                'en' => $validatedData['carousels'][0]['title_en'],
                'ar' => $validatedData['carousels'][0]['title_ar'],
            ], 'subtitle' => [
                'en' => $validatedData['carousels'][0]['subtitle_en'],
                'ar' => $validatedData['carousels'][0]['subtitle_ar'],
            ],
            'product_id' => $validatedData['carousels'][0]['product_id'],

        ]);
        if (request()->allFiles()) {
            if (request()->hasFile('carousels.0.mobile_image')) {
                handleMediaUploads(request()->file('carousels.0.mobile_image'), $model, collectionName: "mobile_carousels", clearExisting: true);

            }
            if (request()->hasFile('carousels.0.image')) {
                handleMediaUploads(request()->file('carousels.0.image'), $model, clearExisting: true);

            }

        }
        return Response::api();
    }

    public function landingSections(Request $request, SettingRepositoryInterface $settingRepository)
    {
        $setting = $settingRepository->query()->where('key',$request->input('key'))->firstOrFail();
        $newValue = $setting->value ? 0 : 1;
        $settingRepository->update([
            'key' => $request->input('key'),
            'value' => $newValue
        ], $setting->id);
        return Response::api();
    }
}


