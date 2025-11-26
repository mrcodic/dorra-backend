<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Carousel\UpdateCarouselRequest;
use App\Http\Requests\SocialLink\UpdateSocialLinkRequest;
use App\Models\{Carousel, GlobalAsset};
use App\Repositories\Interfaces\{CarouselRepositoryInterface,
    CategoryRepositoryInterface,
    LandingReviewRepositoryInterface,
    PaymentMethodRepositoryInterface,
    SettingRepositoryInterface,
    ProductRepositoryInterface,
    SocialLinkRepositoryInterface,
    TemplateRepositoryInterface
};
use App\Traits\HandlesTryCatch;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SettingController extends Controller
{
    use HandlesTryCatch;

    public function details(SocialLinkRepositoryInterface $socialLinkRepository)
    {
        $socialLinks = $socialLinkRepository->all();

        return view("dashboard.settings.details", get_defined_vars());
    }

    public function editDetails(Request $request,SettingRepositoryInterface $settingRepository)
    {
        $validated = $request->validate([

            'phone' => [
                'sometimes',
                'string',
                'max:20',
                'regex:/^\+?[0-9\s().-]{7,20}$/',
            ],

            'store_email' => [
                'sometimes',
                'string',
                'max:254',
                'email:rfc',
            ],
            'order_format' => ['sometimes','string','max:5'],
        ], [
            'phone.regex'        => 'Enter a valid phone number (e.g. +201234567890).',
            'store_email.email'  => 'Enter a valid email address (e.g. support@example.com).',
        ]);

        $rows = collect(['phone','store_email','order_format'])
            ->filter(fn ($k) => array_key_exists($k, $validated))
            ->map(fn ($k) => ['key' => $k, 'value' => $validated[$k]])
            ->values()
            ->all();

        $settingRepository->query()->upsert($rows, ['key'], ['value']);
        Cache::forget('app_settings');
        return Response::api();
    }

    public function notifications(SettingRepositoryInterface $settingRepository)
    {
        // Get all notification settings as [ key => value ]
        $flat = $settingRepository->query()
            ->where('group', 'notifications')
            ->pluck('value', 'key')
            ->all();

        // Build dynamic groups from keys like: customers.new_customer_signed_up.email
        $groups = [];
        foreach ($flat as $key => $val) {
            [$group, $event, $channel] = array_pad(explode('.', $key, 3), 3, null);
            if (!$group || !$event || !in_array($channel, ['email', 'notification'], true)) {
                continue; // skip malformed keys
            }

            $dot   = "{$group}.{$event}";
            $label = Str::headline(str_replace('_', ' ', $event));

            if (!isset($groups[$group][$event])) {
                $groups[$group][$event] = [
                    'label'        => $label,
                    'dot'          => $dot,
                    'email'        => (bool)($flat["{$dot}.email"] ?? false),
                    'notification' => (bool)($flat["{$dot}.notification"] ?? false),
                ];
            } else {
                // If only one channel existed, fill the other
                $groups[$group][$event][$channel] = (bool)$val;
            }
        }

        // Sort for stable UI
        ksort($groups);
        foreach ($groups as $g => $rows) {
            ksort($rows);
            // Reindex numerically for simple @foreach in Blade
            $groups[$g] = array_values($rows);
        }

        return view('dashboard.settings.notifications', compact('groups'));
    }

        public function raedAllNotifications(?DatabaseNotification $notification)
        {
            if ($notification) {
                $notification->markAsRead();
                return Response::api();
            }
            auth()->user()->unreadNotifications->markAsRead();
            return Response::api();
        }
    public function updateNotifications(Request $request,SettingRepositoryInterface $settingRepository)
    {
        $incoming = (array)$request->input('settings', []);
        foreach ($incoming as $key => $val) {
            $settingRepository->query()->updateOrCreate(
                ['key' => $key],
                ['value' => (int)(bool)$val, 'group' => 'notifications']
            );
        }
        return Response::api();
    }

    public function payments(PaymentMethodRepositoryInterface $paymentMethodRepository)
    {
        $paymentMethods = $paymentMethodRepository
            ->query()
            ->with('paymentGateway')
            ->whereActive(true)
            ->where(function ($q) {
                $q->whereHas('paymentGateway', fn($gw) => $gw->active())
                    ->orWhereNull('payment_gateway_id');
            })
            ->get();
        return view("dashboard.settings.payments", get_defined_vars());
    }

    public function togglePayments(Request $request, PaymentMethodRepositoryInterface $paymentMethodRepository, $id)
    {
        $validatedData = $request->validate(['active' => 'required|boolean']);
        $paymentMethodRepository->update($validatedData, $id);
        Cache::forget('payment_methods');
        return Response::api();
    }

    public function website(CategoryRepositoryInterface      $repository,
                            CarouselRepositoryInterface      $carouselRepository,
                            ProductRepositoryInterface       $productRepository,
                            TemplateRepositoryInterface      $templateRepository,
                            LandingReviewRepositoryInterface $landingReviewRepository,
    )
    {

        $categories = $repository->query()->with(['children', 'landingProducts'])->whereNull('parent_id')->isLanding()->get();
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
                        'en' => Arr::get($carouselData, 'title_en'),
                        'ar' => Arr::get($carouselData, 'title_ar'),
                    ],
                    'subtitle' => [
                        'en' => Arr::get($carouselData, 'subtitle_en'),
                        'ar' => Arr::get($carouselData, 'subtitle_ar'),
                    ],
                    'title_color' => Arr::get($carouselData, 'title_color'),
                    'subtitle_color' => Arr::get($carouselData, 'subtitle_color'),
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

    public function socialLinks(UpdateSocialLinkRequest $request, SocialLinkRepositoryInterface $socialLinkRepository)

    {
        $validatedData = $request->validated();
        if (!Arr::get($validatedData, 'socials')) {
            $socialLinkRepository->query()->delete();
            return Response::api();
        }
        collect($validatedData['socials'])->each(function ($socialData) use ($socialLinkRepository) {

            $socialLinkRepository->query()->updateOrCreate(
                ['id' => $socialData['id'] ?? null],
                [
                    'platform' => Arr::get($socialData, 'platform'),
                    'url' => Arr::get($socialData, 'url'),
                ]
            );


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

