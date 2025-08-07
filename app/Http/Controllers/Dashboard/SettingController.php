<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Carousel\UpdateCarouselRequest;
use App\Http\Requests\Template\UpdateTemplateRequest;
use App\Models\GlobalAsset;
use App\Models\Review;
use App\Repositories\Interfaces\CarouselRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\LandingReviewRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\SettingRepositoryInterface;
use App\Repositories\Interfaces\TemplateRepositoryInterface;
use App\Traits\HandlesTryCatch;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SettingController extends Controller
{
    use HandlesTryCatch;

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

    public function website(CategoryRepositoryInterface      $repository,
                            CarouselRepositoryInterface      $carouselRepository,
                            ProductRepositoryInterface       $productRepository,
                            TemplateRepositoryInterface      $templateRepository,
                            LandingReviewRepositoryInterface $landingReviewRepository,
    )
    {
        $categories = $repository->query()->isLanding()->get();
        $carousels = $carouselRepository->all();
        $products = $productRepository->all(columns: ['id', 'name']);
        $templates = $templateRepository->query()->isLanding()->get(['id', 'name']);
        $allCategories = $repository->query()->get();
        $partners = Media::query()->whereCollectionName('partners')->get();
        $reviewsWithImages = $landingReviewRepository->query()->whereType('with_image')->get();
        $reviewsWithoutImages = $landingReviewRepository->query()->whereType('without_image')->get();

        return view("dashboard.settings.website", get_defined_vars());
    }

    public function createOrUpdateCarousel(UpdateCarouselRequest $request, CarouselRepositoryInterface $carouselRepository,$id)
    {
        $validatedData = $request->validated();
        collect($validatedData['carousels'])->each(function ($carouselData, $index) use ($carouselRepository,$id) {

            $model = $carouselRepository->query()->updateOrCreate(
                ['id' =>$id?? null],
                [
                    'title' => [
                        'en' => $carouselData['title_en'],
                        'ar' => $carouselData['title_ar'],
                    ],
                    'subtitle' => [
                        'en' => $carouselData['subtitle_en'],
                        'ar' => $carouselData['subtitle_ar'],
                    ],
                    'product_id' => $carouselData['product_id'],
                ]
            );

            if (request()->hasFile("carousels.$index.mobile_image")) {
                handleMediaUploads(
                    request()->file("carousels.$index.mobile_image"),
                    $model,
                    collectionName: "mobile_carousels",
                    clearExisting: true
                );
            }

            if (request()->hasFile("carousels.$index.image")) {
                handleMediaUploads(
                    request()->file("carousels.$index.image"),
                    $model,
                    collectionName: "carousels",
                    clearExisting: true
                );
            }
        });

        return Response::api();
    }
    public function removeCarousel($id, CarouselRepositoryInterface $carouselRepository)
    {
        $carousel = $carouselRepository->find($id);
        if ($carousel && $carousel->hasMedia()) {
            $carousel->clearMediaCollection();
        }
        $carousel->delete();
        return Response::api();
    }
    public function landingSections(Request $request, SettingRepositoryInterface $settingRepository)
    {
        $setting = $settingRepository->query()->where('key', $request->input('key'))->firstOrFail();
        $newValue = $setting->value ? 0 : 1;
        $settingRepository->update([
            'key' => $request->input('key'),
            'value' => $newValue
        ], $setting->id);
        return Response::api();
    }

    public function updateStatisticsSection(Request $request, SettingRepositoryInterface $settingRepository)
    {
        $validated = $request->validate([
            'customers' => 'required|integer|min:0',
            'orders' => 'required|integer|min:0',
            'rate' => 'required|numeric|min:0|max:5',
        ]);

        collect($validated)->each(function ($value, $key) use ($settingRepository) {
            $settingRepository->query()->where(['key' => $key, 'group' => "statistics_landing"])->update(
                ['value' => $value]
            );
        });

        return Response::api();
    }

    public function uploadPartners(Request $request)
    {
        $request->validate(['image' => ['required', 'file', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048']]);
        $asset = GlobalAsset::create([
            'title' => 'Partner Upload',
            'type' => 'partner_upload'
        ]);
        handleMediaUploads($request->image, $asset, collectionName: "partners");
        return Response::api();

    }

    public function removePartner($id)
    {
        $media = Media::query()->find($id);
        $this->handleTransaction(function () use ($media) {
            $media->model->delete();
            $media->delete();
        });
        return Response::api();
    }

    public function storeReviewsWithImages(Request $request, LandingReviewRepositoryInterface $reviewRepository)
    {
        $validatedData = $request->validate([
            'customer' => 'required|string|max:255',
            'rate' => 'required|integer|min:1|max:5',
            'date' => 'required|date',
            'review' => 'required|string|max:1000',
            'type' => 'required|in:with_image,without_image,other',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $review = $reviewRepository->create($validatedData);
        handleMediaUploads($request->image, $review, collectionName: "reviews_landing_images");
        return Response::api();

    }
    public function removeReview($id, LandingReviewRepositoryInterface $reviewRepository)
    {
        $review = $reviewRepository->find($id);
        if ($review && $review->hasMedia()) {
            $review->clearMediaCollection();
        }
        $review->delete();

        return Response::api();
    }

    public function storeReviews(Request $request, LandingReviewRepositoryInterface $reviewRepository)
    {
        $validatedData = $request->validate([
            'customer' => 'required|string|max:255',
            'rate' => 'required|integer|min:1|max:5',
            'date' => 'required|date',
            'review' => 'required|string|max:1000',
            'type' => 'required|in:with_image,without_image,other',
        ]);
        $reviewRepository->create($validatedData);
        return Response::api();
    }


}


