<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\HttpEnum;
use App\Enums\Mockup\TypeEnum;
use App\Enums\Template\StatusEnum;
use App\Http\Controllers\Base\DashboardController;
use App\Jobs\ImportTemplatesFromExcel;
use App\Models\FontStyle;
use Illuminate\Validation\Rule;
use App\Models\{Template, Mockup};
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Http\Requests\Template\{StoreTemplateRequest,
    UpdateTemplateEditorRequest,
    UpdateTemplateRequest
};
use App\Http\Resources\{MediaResource, Template\TemplateResource};
use App\Repositories\Interfaces\{CategoryRepositoryInterface,
    FlagRepositoryInterface,
    IndustryRepositoryInterface,
    MockupRepositoryInterface,
    ProductRepositoryInterface,
    ProductSpecificationRepositoryInterface,
    TagRepositoryInterface,
    TemplateRepositoryInterface
};
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
        public CategoryRepositoryInterface             $categoryRepository,
        public FlagRepositoryInterface                 $flagRepository,
        public IndustryRepositoryInterface             $industryRepository,
        public MockupRepositoryInterface               $mockupRepository,

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
            'shared' => [
                'products' => $this->productRepository->query()->get(['id', 'name']),
                'categories' => $this->categoryRepository->query()->get(['id', 'name']),
                'industries' => $this->industryRepository->query()->whereNull('parent_id')->get(['id', 'name']),
                'sub_industries' => $this->industryRepository->query()->whereNotNull('parent_id')->get(['id', 'name']),
                'product_with_categories' => $this->categoryRepository->query()->where('is_has_category', 1)->has('products')->get(['id', 'name']),
                'product_without_categories' => $this->categoryRepository->query()->where('is_has_category', 0)->get(['id', 'name']),
                'tags' => $this->tagRepository->query()->get(['id', 'name']),
                'mockups' => $this->mockupRepository->query()->with(['media'])->get(),
            ],
        ];
        $this->methodRelations = [
            'index' => ["media", "categories", "types"],
            'edit' => ['products', 'types']
        ];

    }

    public function index()
    {
        $data = $this->service->getAll($this->getRelations('index'), $this->usePagination, perPage: request('per_page', 16));
        $data->each(function ($template) {
            $template->products->each(function ($product) {
                $product->loadMissing('category');
            });
        });
        $associatedData = $this->getAssociatedData('index');
        if (request()->ajax()) {
            if (request('request_type') === 'api') {
                return Response::api(
                    data: TemplateResource::collection($data)->response()->getData()
                );
            }

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

    public function storeAndRedirect(StoreTemplateRequest $request)
    {
        $template = $this->templateService->storeResource($request->validated());
        if ($request->filled('mockup_id')) {
            return Response::api(data: [
                "mockup_redirect_url" => route('mockups.edit', [
                    'mockup' => $request->mockup_id,
                    'template_id' => $template->id,
                ])
            ]);
        }
        return Response::api(data: [
            "redirect_url" =>
                config('services.editor_url') . 'templates/' . $template->id . '?is_clear'
        ]);
    }

    public function update(Request $request, string $id)
    {
        $rules = $this->updateRequestClass->rules($id);
        $messages = [
            'dimension_id.required' => 'You must choose size',
            'dimension_id.integer' => 'You must choose size',
            'dimension_id.exists' => 'Selected size is invalid',
        ];

        $validated = Validator::make(
            $request->all(),
            $rules,
            $messages
        )->validate();
        $model = $this->service->updateResource($validated, $id);
        if ($request->filled('mockup_id')) {
            return Response::api(data: [
                "mockup_redirect_url" => route('mockups.edit', [
                    'mockup' => $request->mockup_id,
                    'template_id' => $model->id,
                ])
            ]);
        }
        if ($request->boolean('go_to_editor')) {
            return Response::api(data: ['editor_url' => config('services.editor_url') . 'templates/' . $model->id . '?is_clear=1']);
        }

        return Response::api(data: $this->resourceClass::make($model));

    }

    public function updateEditorData(UpdateTemplateEditorRequest $request, Template $template)
    {
        $template = $this->templateService->updateEditorData($request->validated(), $template->id);
        return Response::api(data: $this->resourceClass::make($template));
    }

    public function show($id)
    {
        return Response::api(data: TemplateResource::make($this->templateService->showResource($id,
            ['products.dimensions', 'types',
                'dimension', 'libraryMedia'])));
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
        $template = $this->templateService->updateEditorData(['status' => $request->status], $id);
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

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt'],
            'images' => ['required', 'file', 'mimes:zip'],
        ]);

        $batch = (string)Str::uuid();

        $excelRel = $request->file('file')->storeAs(
            "imports/$batch",
            'sheet.' . $request->file('file')->getClientOriginalExtension(),
            'local'
        );

        $zipRel = $request->file('images')->storeAs(
            "imports/$batch",
            'images.zip',
            'local'
        );

        ImportTemplatesFromExcel::dispatch($excelRel, $zipRel, $batch);

        return Response::api(data: [
            'batch' => $batch,
            'status' => 'queued',
        ]);
    }

    public function toggleBestSeller(Template $template)
    {
        $template->update([
            'is_best_seller' => !$template->is_best_seller
        ]);

        return Response::api(data: TemplateResource::make($template));

    }


    public function attachMultipleLibraryAssets(Request $request, Template $template)
    {
        $validated = $request->validate([
            'library_asset_ids' => 'required|array',
            'library_asset_ids.*' => 'exists:media,id'
        ]);
        $template->libraryMedia()->syncWithoutDetaching($validated['library_asset_ids']);
        return Response::api();
    }

    public function getLibraryAssets(Request $request, Template $template): JsonResponse
    {
        $perPage = $request->get('per_page', 10);

        $paginated = $template->libraryMedia()
            ->wherePivotNull('type')
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());

        return Response::api(data: MediaResource::collection($paginated)->response()->getData(true));
    }


    public function detachLibraryAsset(Template $template, Media $media)
    {
        $template->libraryMedia()->detach($media);
        return Response::api();
    }

    public function attachMultipleFonts(Request $request, Template $template)
    {
        $validated = $request->validate([
            'font_media_id' => ['required', 'exists:font_styles,id']
        ]);

        $fontStyle = FontStyle::find($validated['font_media_id']);
        $firstMedia = $fontStyle->media()->first();

        if (!$firstMedia) {
            return Response::api(HttpEnum::NOT_FOUND, 'No media found for this font style');
        }
        $template->libraryMedia()->syncWithoutDetaching([
            $firstMedia->id => ['type' => 'font']
        ]);
        return Response::api();
    }

    /**
     * Save mockup positions, colors, and upload generated mockup files
     */
    public function savePositionsAndUploadMockups(Template $template, Mockup $mockup, Request $request)
    {
        $request->validate([
            'positions' => ['required', 'array'],
            'positions.*.name' => ['required', 'string', 'max:100',
                Rule::in($mockup->types->map(fn($type) => $type->value->key())->toArray()),
            ],
            'positions.*.p1x' => ['required', 'numeric', 'min:0', 'max:100'],
            'positions.*.p1y' => ['required', 'numeric', 'min:0', 'max:100'],
            'positions.*.p2x' => ['required', 'numeric', 'min:0', 'max:100'],
            'positions.*.p2y' => ['required', 'numeric', 'min:0', 'max:100'],
            'positions.*.p3x' => ['required', 'numeric', 'min:0', 'max:100'],
            'positions.*.p3y' => ['required', 'numeric', 'min:0', 'max:100'],
            'positions.*.p4x' => ['required', 'numeric', 'min:0', 'max:100'],
            'positions.*.p4y' => ['required', 'numeric', 'min:0', 'max:100'],
            'colors' => ['required', 'array'],
            'files' => ['required', 'array'],
            'files.*.side' => [
                'string',
                'max:100',
                Rule::in($mockup->types->map(fn($type) => $type->value->key())->toArray()),
            ],
            'files.*.color' => [
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    $colors = $request->input('colors', []);
                    $normalizedColors = array_map(fn($c) => strtolower(ltrim($c, '#')), $colors);
                    $normalizedValue  = strtolower(ltrim($value, '#'));

                    if (!in_array($normalizedValue, $normalizedColors)) {
                        $fail("The $attribute must be one of the provided colors.");
                    }
                },
            ],
            'files.*.file' => ['image'],
        ]);

        $template->mockups()->syncWithoutDetaching([
            $mockup->id => [
                'positions' => $request->input('positions'),
                'colors' => $request->input('colors')
            ]
        ]);

        $this->uploadMockupFiles($template, $mockup, $request);
        return Response::api();
    }

    /**
     * Upload template model image
     */
    public function uploadTemplateImage(Template $template, Mockup $mockup, Request $request)
    {
        $request->validate([
            'model_image' => ['required', 'image'],
        ]);

        if ($request->hasFile('model_image')) {
            $template->getMedia('rendered_mockups')
                ->filter(fn($t) => $t->getCustomProperty('template_id') == $template->id &&
                    $t->getCustomProperty('category_id') == $mockup->category_id
                )
                ->each->delete();
            $template
                ->addMedia($request->file('model_image'))
                ->usingFileName("tpl_{$template->id}_cat{$mockup->category_id}.png")
                ->withCustomProperties([
                    'template_id' => (string)$template->id,
                    'category_id' => (int)$mockup->category_id,
                ])
                ->toMediaCollection('rendered_mockups');
        }

        return Response::api();
    }

    /**
     * Helper method to upload mockup files
     */
    private function uploadMockupFiles(Template $template, Mockup $mockup, Request $request)
    {
        foreach ($request->input('files') as $index => $fileData) {
            $side = $fileData['side'] ?? 'front';
            $hex = $fileData['color'] ?? '#000000';
            $safeHex = ltrim($hex, '#');
dd(        $mockup->getMedia('generated_mockups')
    ->filter(fn($m) => $m->getCustomProperty('template_id') == $template->id &&
        $m->getCustomProperty('side') == $side &&
        strtolower($m->getCustomProperty('hex')) == strtolower($safeHex)&&
        $m->getCustomProperty('category_id') == $mockup->category_id
    ));
            $mockup->getMedia('generated_mockups')
                ->filter(fn($m) => $m->getCustomProperty('template_id') == $template->id &&
                    $m->getCustomProperty('side') == $side &&
                    strtolower($m->getCustomProperty('hex')) == strtolower($safeHex)&&
                    $m->getCustomProperty('category_id') == $mockup->category_id
                )
                ->each->delete();

            if ($request->hasFile("files.{$index}.file")) {
                $mockup->addMedia($request->file("files.{$index}.file"))
                    ->usingFileName("mockup_{$side}_tpl{$template->id}_{$safeHex}.png")
                    ->withCustomProperties([
                        'side' => $side,
                        'template_id' => (string)$template->id,
                        'hex' => $safeHex,
                        'category_id' => (int)$mockup->category_id,
                    ])
                    ->toMediaCollection('generated_mockups');
            }
        }
    }
}
