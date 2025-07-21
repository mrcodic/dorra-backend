<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\HttpEnum;
use App\Enums\Template\StatusEnum;
use App\Http\Controllers\Base\DashboardController;
use App\Http\Resources\{MediaResource, TemplateResource};
use App\Repositories\Interfaces\{ProductSpecificationRepositoryInterface,
    ProductRepositoryInterface,
    TagRepositoryInterface,
    TemplateRepositoryInterface
};
use App\Services\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, Response};
use App\Http\Requests\Template\{StoreTemplateRequest,
    StoreTranslatedTemplateRequest,
    UpdateTranslatedTemplateRequest
};


class TemplateController extends DashboardController
{
    public function __construct(
        public TemplateService                         $templateService,
        public ProductRepositoryInterface              $productRepository,
        public TemplateRepositoryInterface             $templateRepository,
        public TagRepositoryInterface                  $tagRepository,
        public ProductSpecificationRepositoryInterface $productSpecificationRepository,
        public ProductRepositoryInterface              $productRepositoryInterface,

    )
    {
        parent::__construct($templateService);
        $this->storeRequestClass = new StoreTemplateRequest();
        $this->updateRequestClass = new UpdateTranslatedTemplateRequest();
        $this->indexView = 'templates.index';
        $this->createView = 'templates.create';
        $this->editView = 'templates.edit';
        $this->showView = 'templates.show';
        $this->usePagination = true;
        $this->resourceTable = 'templates';
        $this->resourceClass = TemplateResource::class;
        $this->assoiciatedData = [
            'create' => [
                'products' => $this->productRepository->query()->get(['id', 'name']),
                'tags' => $this->tagRepository->query()->get(['id', 'name']),

            ],
            'index' => [
                'products' => $this->productRepository->query()->get(['id', 'name']),
                'tags' => $this->tagRepository->query()->get(['id', 'name']),
            ],
            'edit' => [
                'products' => $this->productRepository->query()->get(['id', 'name']),
                'tags' => $this->tagRepository->query()->get(['id', 'name']),

            ],
        ];
        $this->methodRelations = [
            'index' => ["tags", "media", "products"],
            'edit' => ['products']
        ];

    }

    public function index()
    {
        $data = $this->service->getAll($this->getRelations('index'), $this->usePagination, perPage: request('per_page', 16));
        $associatedData = $this->getAssociatedData('index');
        if (request()->ajax()) {
            $cards = view('dashboard.partials.filtered-templates', compact('data'))->render();

            $pagination = '';
            if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                $pagination = '<div class="mt-2 px-1">' .
                    $data->withQueryString()->links('pagination::bootstrap-5')->render() .
                    '</div>';
            }

            return Response::api(data: [
                'cards' => $cards,
                'pagination' => $pagination,
                'total' => is_countable($data) ? count($data) : $data->total(),
            ]);
        }

        return view("dashboard.templates.index", get_defined_vars());
    }

    public function storeAndRedirect(StoreTranslatedTemplateRequest $request)
    {
        $template = $this->templateService->storeResource($request->validated());
        return Response::api(data: [
            "redirect_url" =>
                config('services.editor_url') .
                'templates/' . $template->id . "?has_mockup=" .
                ($template->products->contains('has_mockup', true) ? 'true' : 'false')
        ]);
    }

    public function show($id)
    {
        return Response::api(data: TemplateResource::make($this->templateService->showResource($id, ['specifications.options', 'product.prices','products'])));
    }

    public function getProductTemplates()
    {
        $productId = request()->input('productId');
        $templates = $this->templateService->getProductTemplates($productId);
        if (request()->ajax()) {
            if (!$productId) {
                $cacheKey = getOrderStepCacheKey();
                $stepData = Cache::get($cacheKey, []);
                $productId = $stepData['product_id'] ?? null;
            }
            if (!$productId) {
                return Response::api(HttpEnum::BAD_REQUEST, errors: ['error' => 'Product not selected.']);
            }
            $templates = $this->templateRepository->query()->with(['product','products'])
                ->when($productId,function ($query) use ($productId) {
                    $query->whereProductId($productId);
                })->live()->get();
            return view('dashboard.orders.steps.step3', compact('templates'))->render();
        }
        $templateData = TemplateResource::collection($templates)
            ->additional([
                'product' => [
                    'name' => $this->productRepositoryInterface->query()->whereKey($productId)?->value('name')
                ]
            ])
            ->response()
            ->getData(true);

        return Response::api(
            data: $templateData
        );


    }

    public function changeStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required', 'in:' . StatusEnum::getValuesAsString()]);
        $template = $this->templateService->updateResource(['status' => $request->status], $id);
        return Response::api(data: TemplateResource::make($template));
    }

    public function templateAssets()
    {
        $media = $this->templateService->templateAssets();
        return Response::api(data: MediaResource::collection($media)->response()->getData(true));

    }

    public function storeTemplateAssets(Request $request)
    {
        $media = $this->templateService->storeTemplateAssets($request);
        return Response::api(data: MediaResource::make($media));

    }
}
