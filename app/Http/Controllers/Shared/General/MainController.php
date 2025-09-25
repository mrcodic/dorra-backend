<?php

namespace App\Http\Controllers\Shared\General;


use App\Enums\Product\UnitEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dimension\StoreDimensionRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\FlagService;
use App\Http\Resources\{CategoryResource,
    CountryCodeResource,
    CountryResource,
    Design\DesignResource,
    DimensionResource,
    FlagResource,
    FolderResource,
    MediaResource,
    Product\ProductResource,
    StateResource,
    TagResource,
    TeamResource,
    Template\TemplateResource,
    Template\TypeResource
};
use App\Models\CountryCode;
use App\Models\GlobalAsset;
use App\Models\Type;
use App\Repositories\Interfaces\{CategoryRepositoryInterface,
    CountryRepositoryInterface,
    DimensionRepositoryInterface,
    MessageRepositoryInterface,
    ProductRepositoryInterface,
    StateRepositoryInterface,
    TemplateRepositoryInterface
};
use App\Services\CategoryService;
use App\Services\DesignService;
use App\Services\FolderService;
use App\Services\TagService;
use App\Services\TeamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
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

    public function convertFabricJson(Request $request)
    {
        $jsonPath = storage_path('app/fabric-json.json');
        $tempPngPath = storage_path('app/fabric-rendered.png');
        $nodeScriptPath = base_path('fabric-renderer/renderFabric.js');

        try {
            file_put_contents($jsonPath, $request->design_data);
            $cmd = "\"C:\\Program Files\\nodejs\\node.exe\" {$nodeScriptPath} {$jsonPath} {$tempPngPath} 2>&1";

            exec($cmd, $output, $returnVar);

            if ($returnVar !== 0) {
                Log::error('Fabric render job failed', ['cmd' => $cmd, 'output' => implode("\n", $output)]);
                throw new \Exception("Failed to render PNG from Fabric JSON");
            }

            if (!file_exists($tempPngPath)) {
                Log::error('Rendered PNG file missing after node script', ['path' => $tempPngPath]);
                throw new \Exception("Rendered PNG file not found");
            }

            $asset = GlobalAsset::create([
                'title' => 'Fabric Render',
                'type' => 'design-preview'
            ]);

            $assetMedia = $asset->addMedia($tempPngPath)->toMediaCollection('fabric');

        } finally {
            if (file_exists($jsonPath)) {
                unlink($jsonPath);
            }
            if (file_exists($tempPngPath)) {
                unlink($tempPngPath);
            }
        }
        return Response::api(data: MediaResource::make($assetMedia));
    }

    public function addMedia(Request $request, $id = null)
    {
//        $global  = GlobalAsset::create(['title' => $request->title, 'type' => $request->type]);
////        $model = ($request->resource)::find($id);
        $media = handleMediaUploads($request->allFiles(), null, clearExisting: true);
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
        $locale = app()->getLocale();
        $rates = $request->rates;
        $categories = $this->categoryRepository->query()->with([
            'products' => function ($query) use ($request, $locale) {
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?", [
                    '%' . strtolower($request->search) . '%'
                ])->when($request->rates, fn($q) => $q->withReviewRating($request->rates));
            },
            'products.media', 'media',
            'templates.tags' => function ($query) use ($locale,$request) {
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?", [
                    '%' . strtolower($request->search) . '%'
                ]);
            },
            'products.templates.tags' => function ($query) use ($locale, $request) {
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?", [
                    '%' . strtolower($request->search) . '%'
                ]);
            },
            ])->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?", [
                '%' . strtolower($request->search) . '%'
            ])
            ->when($request->take, function ($query, $take) {
                $query->take($take);
            })
            ->when($rates, function ($q) use ($rates) {
                $placeholders = implode(',', array_fill(0, count($rates), '?'));
                $q->where(function ($qq) use ($rates, $placeholders) {
                    $qq->whereHas('products', fn ($p) => $p->withReviewRating($rates))
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
        return DimensionResource::collection($model->dimensions);
    }

}
