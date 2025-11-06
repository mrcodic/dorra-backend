<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Carousel\UpdateCarouselRequest;
use App\Models\{Carousel,GlobalAsset};
use App\Repositories\Interfaces\{CarouselRepositoryInterface,
    CategoryRepositoryInterface,
    LandingReviewRepositoryInterface,
    PaymentMethodRepositoryInterface,
    SettingRepositoryInterface,
    ProductRepositoryInterface,
    TemplateRepositoryInterface,
};
use App\Traits\HandlesTryCatch;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SettingController extends Controller
{
    use HandlesTryCatch;

    public function details()
    {
        return view("dashboard.settings.details");
    }

    public function notifications()
    {
        return view("dashboard.settings.notifications");
    }

    public function payments(PaymentMethodRepositoryInterface $paymentMethodRepository)
    {
        $paymentMethods = $paymentMethodRepository->all();
        return view("dashboard.settings.payments",get_defined_vars());
    }

    public function togglePayments(Request $request,PaymentMethodRepositoryInterface $paymentMethodRepository,$id)
    {
        $validatedData = $request->validate(['active' => 'required|boolean']);
        $paymentMethodRepository->update($validatedData,$id);
        return Response::api();
}
    public function website(CategoryRepositoryInterface      $repository,
                            CarouselRepositoryInterface      $carouselRepository,
                            ProductRepositoryInterface       $productRepository,
                            TemplateRepositoryInterface      $templateRepository,
                            LandingReviewRepositoryInterface $landingReviewRepository,
    )
    {

        $categories = $repository->query()->with(['children','landingProducts'])->whereNull('parent_id')->isLanding()->get();
        $categoriesCarousels = $repository->query()->whereNull('parent_id')->whereIsHasCategory(0)->get();
        $carousels = $carouselRepository->all();
        $products = $productRepository->all(columns: ['id', 'name']);
        $templates = $templateRepository->query()->isLanding()->get(['id', 'name']);
        $allCategories = $repository->query()->whereNull('parent_id')->get();
        $partners = Media::query()->whereCollectionName('partners')->get();
        $reviewsWithImages = $landingReviewRepository->query()->whereType('with_image')->get();
        $reviewsWithoutImages = $landingReviewRepository->query()->whereType('without_image')->get();

        return view("dashboard.settings.website", get_defined_vars());
    }

    public function createOrUpdateCarousel(UpdateCarouselRequest $request, CarouselRepositoryInterface $carouselRepository)

    {
        $validatedData = $request->validated();

        collect($validatedData['carousels'])->each(function ($carouselData) use ($carouselRepository) {

            // Create or update carousel
            $carousel = $carouselRepository->query()->updateOrCreate(
                ['id' => $carouselData['id'] ?? null],
                [
                    'title' => [
                        'en' =>Arr::get($carouselData, 'title_en'),
                        'ar' => Arr::get($carouselData, 'title_ar'),
                    ],
                    'subtitle' => [
                        'en' =>Arr::get($carouselData, 'subtitle_en'),
                        'ar' => Arr::get($carouselData, 'subtitle_ar'),
                    ],
                    'title_color'    => Arr::get($carouselData, 'title_color'),
                    'subtitle_color'    => Arr::get($carouselData, 'subtitle_color'),
                    'product_id' => Arr::get($carouselData, 'product_id'),
                    'category_id' => Arr::get($carouselData, 'category_id'),
                ]
            );

            // Attach website media
            if (Arr::get($carouselData, 'website_media_ids')) {
                $carousel->clearMediaCollection('carousels');
                Media::whereIn('id', $carouselData['website_media_ids'])
                    ->update([
                        'model_type' => Carousel::class,
                        'model_id' => $carousel->id,
                        'collection_name' => 'carousels',
                    ]);
            }
            if (Arr::get($carouselData, 'website_ar_media_ids')) {
                $carousel->clearMediaCollection('carousels_ar');
                Media::whereIn('id', $carouselData['website_ar_media_ids'])
                    ->update([
                        'model_type' => Carousel::class,
                        'model_id' => $carousel->id,
                        'collection_name' => 'carousels_ar',
                    ]);
            }

            // Attach mobile media
            if (Arr::get($carouselData, 'mobile_media_ids')) {
                $carousel->clearMediaCollection('mobile_carousels');
                Media::whereIn('id', $carouselData['mobile_media_ids'])
                    ->update([
                        'model_type' => Carousel::class,
                        'model_id' => $carousel->id,
                        'collection_name' => 'mobile_carousels',
                    ]);
            }
            if (Arr::get($carouselData, 'mobile_ar_media_ids')) {
                $carousel->clearMediaCollection('mobile_carousels_ar');
                Media::whereIn('id', $carouselData['mobile_ar_media_ids'])
                    ->update([
                        'model_type' => Carousel::class,
                        'model_id' => $carousel->id,
                        'collection_name' => 'mobile_carousels_ar',
                    ]);
            }
        });

        return Response::api();
    }

    public function removeCarousel($id, CarouselRepositoryInterface $carouselRepository)
    {
        $carousel = $carouselRepository->find($id);
        if ($carousel && $carousel->hasMedia()) {
            $carousel->clearMediaCollection('mobile_carousels');
            $carousel->clearMediaCollection('carousels');
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
        $request->validate([
            'media_id' => 'required|integer|exists:media,id',
        ]);
        $asset = GlobalAsset::create([
            'title' => 'Partner Upload',
            'type' => 'partner_upload'
        ]);
        if ($request->media_id) {
            Media::where('id', $request->media_id)
                ->update([
                    'model_type' => get_class($asset),
                    'model_id' => $asset->id,
                    'collection_name' => 'partners',
                ]);

        }

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
            'image_id' => 'required|exists:media,id',
        ]);

        $review = $reviewRepository->create($validatedData);
        Media::where('id', $validatedData['image_id'])
            ->update([
                'model_type' => get_class($review),
                'model_id' => $review->id,
                'collection_name' => 'reviews_landing_images',
            ]);
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

