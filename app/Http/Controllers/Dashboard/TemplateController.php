<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\HttpEnum;
use App\Enums\Template\StatusEnum;
use App\Http\Controllers\Base\DashboardController;
use App\Models\Template;
use App\Http\Requests\Template\{StoreTemplateRequest,
    StoreTranslatedTemplateRequest,
    UpdateTemplateEditorRequest,
    UpdateTemplateRequest
};
use App\Http\Resources\{MediaResource, Template\TemplateResource};
use App\Repositories\Interfaces\{CategoryRepositoryInterface,
    FlagRepositoryInterface,
    ProductRepositoryInterface,
    ProductSpecificationRepositoryInterface,
    TagRepositoryInterface,
    TemplateRepositoryInterface};
use App\Services\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, Response, Validator};


class TemplateController extends DashboardController
{
    public function __construct(
        public TemplateService                         $templateService,
        public ProductRepositoryInterface              $productRepository,
        public TemplateRepositoryInterface             $templateRepository,
        public TagRepositoryInterface                  $tagRepository,
        public ProductSpecificationRepositoryInterface $productSpecificationRepository,
        public ProductRepositoryInterface              $productRepositoryInterface,
        public CategoryRepositoryInterface              $categoryRepository,
        public FlagRepositoryInterface                  $flagRepository,

    )
    {
        parent::__construct($templateService);
        $this->storeRequestClass = new StoreTemplateRequest();
        $this->updateRequestClass = new UpdateTemplateRequest();
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
                'product_with_categories' => $this->categoryRepository->query()->where('is_has_category',1)->has('products')->get(['id', 'name']),
                'product_without_categories' => $this->categoryRepository->query()->where('is_has_category',0)->get(['id', 'name']),
                'tags' => $this->tagRepository->query()->get(['id', 'name']),
                'flags' => $this->flagRepository->query()->get(['id', 'name']),

            ],
            'index' => [
                'products' => $this->productRepository->query()->get(['id', 'name']),
                'tags' => $this->tagRepository->query()->get(['id', 'name']),
            ],
            'edit' => [
                'products' => $this->productRepository->query()->get(['id', 'name']),
                'product_with_categories' => $this->categoryRepository->query()->where('is_has_category',1)->has('products')->get(['id', 'name']),
                'product_without_categories' => $this->categoryRepository->query()->where('is_has_category',0)->get(['id', 'name']),
                'tags' => $this->tagRepository->query()->get(['id', 'name']),
                'flags' => $this->flagRepository->query()->get(['id', 'name']),

            ],
        ];
        $this->methodRelations = [
            'index' => ["tags", "media", "products", "types"],
            'edit' => ['products', 'types']
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
                config('services.editor_url') . 'templates/' . $template->id . '?is_clear'
        ]);
    }


    public function update(Request $request, string $id)
    {
        // Validate (this will throw a 422 JSON if the client sends Accept: application/json)
        $rules     = $this->updateRequestClass->rules($id);
        $validated = Validator::make($request->all(), $rules)->validate();
       // Save/update
        $model = $this->service->updateResource($validated, $id);

        // If the caller clicked “Save & Edit”
        if ($request->boolean('go_to_editor')) {
            $editorUrl = config('services.editor_url') . 'templates/' . $model->id . '?is_clear=1';

            // ✅ For normal form submit: do an HTTP redirect (302 is expected)
            return redirect($editorUrl);
        }

        // Normal JSON response
        if ($request->wantsJson() || $request->ajax()) {
            return Response::api(data: $this->resourceClass::make($model));
        }

        // Or normal web flow (no redirect away)
        return redirect()
            ->route('templates.edit', $model->id)
            ->with('status', 'Saved!');
    }


    public function updateEditorData(UpdateTemplateEditorRequest $request, $id)
    {

        $template = $this->templateService->updateResource($request->validated(), $id);
        return Response::api(data: $this->resourceClass::make($template));
    }
    public function show($id)
    {
        return Response::api(data: TemplateResource::make($this->templateService->showResource($id, ['products.dimensions', 'types','dimension'])));
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
            $templates = $this->templateRepository->query()->with(['products'])
                ->when($productId, function ($query) use ($productId) {
                    $query->whereHas('products', function ($q) use ($productId) {
                        $q->where('products.id', $productId);
                    });
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

    public function search(Request $request)
    {
        $templates = $this->templateService->search($request);
        return $this->resourceClass::collection($templates);
    }

    public function addToLanding(Request $request)
    {
        $request->validate([
            'design_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = Template::whereKey($value)
                        ->where('status', StatusEnum::LIVE)
                        ->exists();

                    if (!$exists) {
                        $fail('The selected design is invalid or not live.');
                    }
                }
            ],
        ]);

        $template = $this->templateService->addToLanding($request->get('design_id'));
        return $this->resourceClass::make($template);
    }

    public function removeFromLanding(Request $request)
    {
        $request->validate(['design_id' => 'required', 'exists:templates,id']);
        $template = $this->templateService->removeFromLanding($request->get('design_id'));
        return $this->resourceClass::make($template);

    }
}
