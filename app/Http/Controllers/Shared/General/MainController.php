<?php

namespace App\Http\Controllers\Shared\General;


use App\Enums\Product\UnitEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dimension\StoreDimensionRequest;
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
    ZoneRepositoryInterface};
use App\Services\CategoryService;
use App\Services\DesignService;
use App\Services\FolderService;
use App\Services\TagService;
use App\Services\TeamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class MainController extends Controller
{
    public function __construct(
        public CountryRepositoryInterface   $countryRepository,
        public StateRepositoryInterface     $stateRepository,
        public CategoryService              $categoryService,
        public TagService                   $tagService,
        public FlagService                  $flagService,
        public DesignService                $designService,
        public FolderService                $folderService,
        public DimensionRepositoryInterface $dimensionRepository,
        public TeamService                  $teamService,
        public ProductRepositoryInterface   $productRepository,
        public TemplateRepositoryInterface  $templateRepository,
        public CategoryRepositoryInterface  $categoryRepository,
        public StationStatusRepository      $stationStatusRepository,
        public ZoneRepositoryInterface        $zoneRepository,
        public SettingRepositoryInterface      $settingRepository,
        public IndustryRepositoryInterface    $industryRepository,
        public SocialLinkRepositoryInterface $socialLinkRepository,
        public MockupService $mockupService,
        public FontService $fontService,

    )
    {
    }


    public function removeMedia(Media $media)
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
        return Response::api(data: FontResource::collection($this->fontService->getAll(['fontStyles.media','fontStyles.font'],true,perPage: request('per_page')))->response()->getData());

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
        if (auth()->check()) {
            return response()->json(['message' => 'authenticated.'], 200);
        }
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
        $modelId = (int) $model;
        $class   = 'App\\Models\\' . Str::studly($modelName);
        $model = $modelName && $modelId ? $class::find($modelId) : null;
        $media = handleMediaUploads($request->allFiles(), $model, clearExisting: true);
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

    public function contactUs(Request $request, MessageRepositoryInterface $messageRepository)
    {
        $validatedData = $request->validate([
            'email' => 'required|email'
            , 'message' => 'required'
            , 'name' => 'required',
            'phone' => 'required',
        ]);
        $messageRepository->create($validatedData);
        return Response::api();
    }


    public function publicSearch(Request $request)
    {
        $term = trim((string)($request->search ?? ''));
        $rates = $request->rates;

        $locales = config('app.locales', []);
        $terms = collect(preg_split('/[\s,;]+/u', $term))
            ->map(fn($t) => mb_strtolower($t))
            ->filter()
            ->unique()
            ->values();


        $nameExprs = collect($locales)->map(
            fn($loc) => "LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$loc}\"')))"
        );


        $applyContainsAnyLocale = function ($q) use ($terms, $nameExprs) {
            if ($terms->isEmpty()) return;
            $q->where(function ($qq) use ($terms, $nameExprs) {
                foreach ($nameExprs as $expr) {
                    foreach ($terms as $w) {
                        if (hasMeaningfulSearch($w)) {
                            $qq->orWhereRaw("$expr LIKE ?", ['%' . $w . '%']);
                        } else {
                            $qq->whereRaw('1 = 0');
                        }
                    }
                }
            });
        };

        $categories = $this->categoryRepository->query()
            ->with([
                'products' => function ($query) use ($request) {
                    $query->when($request->rates, fn($q) => $q->withReviewRating($request->rates));
                },
                'products.media',
                'media',
                'templates.tags' => function ($query) use ($applyContainsAnyLocale) {
                    $applyContainsAnyLocale($query);
                },
                'products.templates.tags' => function ($query) use ($applyContainsAnyLocale) {
                    $applyContainsAnyLocale($query);
                },
                'templates.industries' => function ($query) use ($applyContainsAnyLocale) {
                    $applyContainsAnyLocale($query);
                },
                'products.templates.industries' => function ($query) use ($applyContainsAnyLocale) {
                    $applyContainsAnyLocale($query);
                },

            ])
            ->where(function ($query) use ($applyContainsAnyLocale) {
                $applyContainsAnyLocale($query);
                $query->orWhereHas('products', function ($q) use ($applyContainsAnyLocale) {
                    $applyContainsAnyLocale($q);
                });
            })
            ->when($request->take, fn($q, $take) => $q->take($take))
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
