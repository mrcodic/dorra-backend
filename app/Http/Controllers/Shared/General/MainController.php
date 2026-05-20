<?php

namespace App\Http\Controllers\Shared\General;


use App\Enums\Product\UnitEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dimension\StoreDimensionRequest;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Repositories\Implementations\StationStatusRepository;
use App\Services\FlagService;
use App\Services\FontService;
use App\Services\MockupService;
use App\Http\Resources\{CategoryResource,
    CountryCodeResource,
    CountryResource,
    Design\DesignResource,
    DimensionResource,
    DiscountCodeResource,
    FlagResource,
    FolderResource,
    FontResource,
    MediaResource,
    MockupResource,
    Product\ProductResource,
    SettingResource,
    SocialLinkResource,
    StateResource,
    StationStatusResource,
    TagResource,
    TeamResource,
    Template\TemplateResource,
    Template\TypeResource};
use App\Models\CountryCode;
use App\Models\GlobalAsset;
use App\Models\Type;
use App\Repositories\Interfaces\{CategoryRepositoryInterface,
    CountryRepositoryInterface,
    DimensionRepositoryInterface,
    IndustryRepositoryInterface,
    MessageRepositoryInterface,
    MockupRepositoryInterface,
    ProductRepositoryInterface,
    SettingRepositoryInterface,
    SocialLinkRepositoryInterface,
    StateRepositoryInterface,
    TemplateRepositoryInterface,
    ZoneRepositoryInterface
};
use App\Services\CategoryService;
use App\Services\DesignService;
use App\Services\FolderService;
use App\Services\TagService;
use App\Services\TeamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class MainController extends Controller
{
    public function __construct(
        public CountryRepositoryInterface    $countryRepository,
        public StateRepositoryInterface      $stateRepository,
        public CategoryService               $categoryService,
        public TagService                    $tagService,
        public FlagService                   $flagService,
        public DesignService                 $designService,
        public FolderService                 $folderService,
        public DimensionRepositoryInterface  $dimensionRepository,
        public TeamService                   $teamService,
        public ProductRepositoryInterface    $productRepository,
        public TemplateRepositoryInterface   $templateRepository,
        public CategoryRepositoryInterface   $categoryRepository,
        public StationStatusRepository       $stationStatusRepository,
        public ZoneRepositoryInterface       $zoneRepository,
        public SettingRepositoryInterface    $settingRepository,
        public IndustryRepositoryInterface   $industryRepository,
        public SocialLinkRepositoryInterface $socialLinkRepository,
        public MockupService                 $mockupService,
        public FontService                   $fontService,

    )
    {
    }


    public function removeMedia(Media $media)
    {
        $notAuth = request()->is('api/v1/admin/*');
        $user = $notAuth ? Admin::first() : getAuthOrGuest();
        abort_unless((int)$media->model_id === (int)$user->id, 403);
        if (empty($media->model_type) && empty($media->model_id)) {
            $media->deleteQuietly();
        } else {
            $media->delete();
        }

        return Response::api();
    }

    public function removeMediaFromDashboard(Media $media)
    {
        if (empty($media->model_type) && empty($media->model_id)) {
            $media->deleteQuietly();
        } else {
            $media->delete();
        }

        return Response::api();
    }


    public function mockups()
    {
        return Response::api(data: $this->mockupService->getMockups());

    }

    public function fonts()
    {
        return Response::api(data: FontResource::collection($this->fontService->getAll(['fontStyles.media', 'fontStyles.font'],
            request('paginate',false), perPage: request('per_page',10))
        )->response()->getData());

    }

    public function discountCode()
    {
        $user = auth('sanctum')->user();
        if ($user->discount_code_id && $user->created_at->addMonth()->isPast())
        {
            $user->update(['discount_code_id' => null]);
        }
        return Response::api( data: $user->discountCode ? DiscountCodeResource::make($user->discountCode): collect([]));
    }
    public function countries()
    {
        return Response::api(data: CountryResource::collection($this->countryRepository->all()));
    }



    public function states()
    {
        return Response::api(data: StateResource::collection($this->stateRepository->getWithFilters()));
    }

    public function zones()
    {
        return Response::api(data: StateResource::collection($this->zoneRepository->getWithFilters()));
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
        if (auth('web')->check()) {
            return response()->json(['message' => 'authenticated.'], 200);
        }
        return response()->json(['message' => 'Unauthenticated.'], 401);

    }

    public function tags()
    {
        return Response::api(data: TagResource::collection($this->tagService->getAll(columns: ['id', 'name'])));

    }

    public function flags()
    {
        return Response::api(data: FlagResource::collection($this->flagService->getAll(columns: ['id', 'name'])));

    }

    public function units()
    {
        return Response::api(data: UnitEnum::toArray());

    }

    public function templateTypes()
    {
        return Response::api(data: TypeResource::collection(Type::all(['id', 'value'])));

    }

    public function addMedia(Request $request, $modelName = null, $model = null)
    {
        $modelId = (int)$model;
        $class = 'App\\Models\\' . Str::studly($modelName);
        $model = $modelName && $modelId ? $class::find($modelId) : null;
        $media = handleMediaUploads($request->allFiles(), $model,
            customProperties: $request->customProperties ?? []
            , clearExisting: true);
        return Response::api(data: MediaResource::make($media));
    }

    public function trash()
    {
        return Response::api(data: [
            'designs' => DesignResource::collection($this->designService->trash()),
            'folders' => FolderResource::collection($this->folderService->trash()),
            'teams' => TeamResource::collection($this->teamService->trash())
        ]);

    }

    public function storeDimension(StoreDimensionRequest $request)
    {
        return Response::api(data: ['id' => uniqid()]);
    }


    private function verifyRecaptchaEnterprise(
        Request $request,
        string $token,
        string $expectedAction = 'contact_us'
    ): void {
        $projectId = config('services.recaptcha_enterprise.project_id');
        $siteKey = config('services.recaptcha_enterprise.site_key');
        $apiKey = config('services.recaptcha_enterprise.api_key');
        $minScore = (float) config('services.recaptcha_enterprise.min_score', 0.5);

        if (!$projectId || !$siteKey || !$apiKey) {
            throw ValidationException::withMessages([
                'recaptcha_token' => ['reCAPTCHA Enterprise is not configured.'],
            ]);
        }

        $url = "https://recaptchaenterprise.googleapis.com/v1/projects/{$projectId}/assessments?key={$apiKey}";

        $response = Http::post($url, [
            'event' => [
                'token' => $token,
                'siteKey' => $siteKey,
                'expectedAction' => $expectedAction,
                'userAgent' => $request->userAgent(),
                'userIpAddress' => $request->ip(),
            ],
        ]);

        $data = $response->json();

        Log::info('recaptcha enterprise assessment', [
            'status' => $response->status(),
            'response' => $data,
        ]);

        $valid = (bool) data_get($data, 'tokenProperties.valid', false);
        $invalidReason = data_get($data, 'tokenProperties.invalidReason');
        $action = data_get($data, 'tokenProperties.action');
        $score = (float) data_get($data, 'riskAnalysis.score', 0);

        if (!$response->successful()) {
            throw ValidationException::withMessages([
                'recaptcha_token' => ['reCAPTCHA Enterprise request failed.'],
            ]);
        }

        if (!$valid) {
            throw ValidationException::withMessages([
                'recaptcha_token' => [
                    'reCAPTCHA verification failed: ' . ($invalidReason ?: 'invalid token'),
                ],
            ]);
        }

        if ($action !== $expectedAction) {
            throw ValidationException::withMessages([
                'recaptcha_token' => ['reCAPTCHA action mismatch.'],
            ]);
        }

        if ($score < $minScore) {
            throw ValidationException::withMessages([
                'recaptcha_token' => ['reCAPTCHA score is too low.'],
            ]);
        }
    }
    /**
     * @throws ValidationException
     */
    public function contactUs(Request $request, MessageRepositoryInterface $messageRepository)
    {
        $validatedData = $request->validate([
            'email'            => 'required|email',
            'message'          => 'required',
            'name'             => 'required',
            'phone'            => 'required',
            'recaptcha_token'  => 'required|string',
        ]);

        $this->verifyRecaptchaEnterprise(
            $request,
            $validatedData['recaptcha_token'],
        );

        $messageRepository->create($validatedData);

        return Response::api();
    }


    public function publicSearch(Request $request)
    {
        $term  = trim((string)($request->search ?? ''));
        $rates = $request->rates;
        $take  = min((int)($request->take ?? 20), 50);

        $locales = config('app.locales', []);
        $terms = collect(preg_split('/[\s,;]+/u', $term))
            ->map(fn($t) => mb_strtolower($t))
            ->filter(fn($t) => hasMeaningfulSearch($t))
            ->unique()
            ->values();

        if ($terms->isEmpty()) {
            return Response::api(data: []);
        }

        $nameExprs = collect($locales)->map(
            fn($loc) => "LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$loc}\"')))"
        );

        // For JSON translatable columns (categories, products)
        $applyContainsAnyLocale = function ($q) use ($terms, $nameExprs) {
            $q->where(function ($qq) use ($terms, $nameExprs) {
                foreach ($nameExprs as $expr) {
                    foreach ($terms as $w) {
                        $qq->orWhereRaw("$expr LIKE ?", ['%' . $w . '%']);
                    }
                }
            });
        };

        // For plain string columns (tags, industries)
        $applyPlainSearch = function ($q) use ($terms) {
            $q->where(function ($qq) use ($terms) {
                foreach ($terms as $w) {
                    $qq->orWhere('name', 'LIKE', '%' . $w . '%');
                }
            });
        };

        $categories = $this->categoryRepository->query()
            // -------------------------------------------------------
            // Only load what the UI renders — NO tags/industries here
            // Filtering via whereHas below handles the search logic
            // -------------------------------------------------------
            ->with([
                'media',
                'products' => function ($q) use ($request) {
                    $q->when($request->rates, fn($q) => $q->withReviewRating($request->rates));
                    $q->limit(10);
                },
                'products.media',
            ])
            // -------------------------------------------------------
            // SEARCH FILTER — pure SQL, zero memory cost
            // -------------------------------------------------------
            ->where(function ($query) use ($applyContainsAnyLocale, $applyPlainSearch) {
                // match category name
                $applyContainsAnyLocale($query);
                $query->orWhereHas('templates', fn($q) => $applyContainsAnyLocale($q));

                // match product name
                $query->orWhereHas('products', fn($q) => $applyContainsAnyLocale($q));

                // match category templates tags
                $query->orWhereHas('templates.tags', fn($q) => $applyContainsAnyLocale($q));

                // match category templates industries
                $query->orWhereHas('templates.industries', fn($q) => $applyContainsAnyLocale($q));

                // match product templates tags
                $query->orWhereHas('products.templates.tags', fn($q) => $applyContainsAnyLocale($q));

                // match product templates industries
                $query->orWhereHas('products.templates.industries', fn($q) => $applyContainsAnyLocale($q));
            })
            ->when($rates, function ($q) use ($rates) {
                $placeholders = implode(',', array_fill(0, count($rates), '?'));
                $q->where(function ($qq) use ($rates, $placeholders) {
                    $qq->whereHas('products', fn($p) => $p->withReviewRating($rates))
                        ->orWhereHas('reviews', function ($rq) use ($rates, $placeholders) {
                            $rq->select('reviewable_id')
                                ->groupBy('reviewable_id', 'reviewable_type')
                                ->havingRaw("ROUND(AVG(rating)) IN ($placeholders)", $rates);
                        });
                });
            })
            ->take($take)
            ->get();

        return Response::api(data: CategoryResource::collection($categories));
    }


    public function dimensions(Request $request)
    {
        $validatedData = $request->validate([
            'resource_id' => ['required', Rule::when($request->resource_type == 'product', function () {
                Rule::exists('products', 'resource_id');
            }, Rule::exists('categories', 'id'))],
            'resource_type' => ['required', 'in:product,category'],
        ]);
        $allowedTypes = [
            'product' => Product::class,
            'category' => Category::class,
        ];
        $modelClass = $allowedTypes[$validatedData['resource_type']];
        $model = $modelClass::findOrFail($validatedData['resource_id']);
        return Response::api(data: DimensionResource::collection($model->dimensions));

    }

    public function getDimensions(Request $request)
    {
        // 1) Validate input
        $data = $request->validate([
            'resource_ids' => ['required', 'array', 'min:1'],
            'resource_ids.*' => ['required', 'integer'],
            'resource_types' => ['required', 'array'],
            'resource_types.*' => ['required', 'in:product,category'],
            'has_corner' => ['nullable', 'in:0,1'],
        ]);

        // Ensure parallel arrays align
        if (count($data['resource_ids']) !== count($data['resource_types'])) {
            throw ValidationException::withMessages([
                'resource_ids' => 'resource_ids and resource_types must be the same length.',
                'resource_types' => 'resource_ids and resource_types must be the same length.',
            ]);
        }

        // 2) Group ids by type
        $idsByType = ['product' => [], 'category' => []];
        foreach ($data['resource_ids'] as $i => $id) {
            $type = $data['resource_types'][$i];
            $idsByType[$type][] = (int)$id;
        }

        // 3) Batch fetch with dimensions (avoid N+1)
        $products = !empty($idsByType['product'])
            ? Product::with('dimensions')->whereIn('id', array_unique($idsByType['product']))->get()
            : collect();

        $categories = !empty($idsByType['category'])
            ? Category::with('dimensions')->whereIn('id', array_unique($idsByType['category']))->get()
            : collect();

        // 4) Validate existence against request (nice error messages)
        $missing = [];
        $foundProducts = $products->pluck('id')->all();
        $foundCategories = $categories->pluck('id')->all();

        foreach ($data['resource_ids'] as $i => $id) {
            $type = $data['resource_types'][$i];
            if ($type === 'product' && !in_array((int)$id, $foundProducts, true)) {
                $missing["resource_ids.$i"] = "Product not found: $id";
            }
            if ($type === 'category' && !in_array((int)$id, $foundCategories, true)) {
                $missing["resource_ids.$i"] = "Category not found: $id";
            }
        }

        if (!empty($missing)) {
            throw ValidationException::withMessages($missing);
        }

        // 5) Flatten & dedupe dimensions
        $dimensions = collect()
            ->merge($products->flatMap->dimensions)
            ->merge($categories->flatMap->dimensions)
            ->unique('id')
            ->values();


//        $hasCorner = $request->has_corner;
//
//        if (!$hasCorner) {
//            $dimensions = $dimensions
//                ->filter(fn($d) =>$d->height == $d->width)
//                ->values();
//        }

        return DimensionResource::collection($dimensions);

    }


    public function stationStatuses(Request $request)
    {
        $statuses = $this->stationStatusRepository->query()->whereStationId($request->station_id)->get();
        return Response::api(data: StationStatusResource::collection($statuses));
    }

    public function generalSettings()
    {
        return Response::api(data: SettingResource::collection($this->settingRepository->query()
            ->whereGroup('general_setting')
            ->get(['id', 'key', 'value'])));
    }

    public function socialLinks()
    {
        return Response::api(data: SocialLinkResource::collection($this->socialLinkRepository->query()
            ->get(['id', 'platform', 'url'])));
    }
}
