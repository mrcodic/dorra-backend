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
                config('services.editor_url') . 'templates/' . $template->id . '?is_clear&product_id='.request('product_without_category_id')
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
            return Response::api(data: ['editor_url' => config('services.editor_url') . 'templates/' . $model->id . '?is_clear=1&product_id='.request('product_without_category_id')]);
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
                'dimension', 'libraryMedia','mockups'])));
    }

    public function getProductTemplates()
    {
        $productId = request()->input('product_without_category_id');
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
        $template = $this->templateService->changeStatus(['status' => $request->status], $id);
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

        // ← Safe null handling for new records
        $oldColors = $template->mockups()
            ->where('mockup_id', $mockup->id)
            ->first()?->pivot->colors ?? [];

        if ($template->mockups()->where('mockup_id', $mockup->id)->exists()) {
            $template->mockups()->updateExistingPivot($mockup->id, [
                'positions' => $request->input('positions'),
                'colors'    => $request->input('colors'),
            ]);
        } else {
            $template->mockups()->attach($mockup->id, [
                'positions' => $request->input('positions'),
                'colors'    => $request->input('colors'),
            ]);
        }

        $this->uploadMockupFiles($template, $mockup, $request, $oldColors);
        return Response::api();
    }

    private function uploadMockupFiles(Template $template, Mockup $mockup, Request $request, array $oldColors): void
    {
        $pivotMockup = $template->mockups()->where('mockup_id', $mockup->id)->first();

        $newColors = collect($pivotMockup?->pivot?->colors ?? [])
            ->map(fn($c) => $this->normalizeHex($c))
            ->filter()->unique()->values()->all();

        $oldColors = collect($oldColors)
            ->map(fn($c) => $this->normalizeHex($c))
            ->filter()->unique()->values()->all();

        $removedColors = array_values(array_diff($oldColors, $newColors));

        $modelColor = $pivotMockup?->pivot?->model_color
            ? $this->normalizeHex($pivotMockup->pivot->model_color)
            : null;

        /*
        |--------------------------------------------------------------------------
        | 1) Delete media for removed colors (including their model images)
        |--------------------------------------------------------------------------
        */
        if (!empty($removedColors)) {
            $mockup->media()
                ->where('collection_name', 'generated_mockups')
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [(string)$template->id])
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.category_id')) = ?", [(string)$mockup->category_id])
                ->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex'))) IN (" . implode(',', array_fill(0, count($removedColors), '?')) . ")", $removedColors)
                ->cursor()
                ->each->delete();
        }

        /*
        |--------------------------------------------------------------------------
        | 2) If model color changed, delete old model image
        |--------------------------------------------------------------------------
        */
        $mockup->media()
            ->where('collection_name', 'generated_mockups')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [(string)$template->id])
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.model_image')) = ?", ['1'])
            ->when($modelColor, fn($q) =>
                // keep only if hex matches current model color, delete the rest
            $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex'))) != ?", [$modelColor])
            )
            ->cursor()
            ->each->delete();

        /*
        |--------------------------------------------------------------------------
        | 3) Replace uploaded files for exact same side + color
        |--------------------------------------------------------------------------
        */
        foreach ($request->input('files', []) as $index => $fileData) {
            if (!$request->hasFile("files.{$index}.file")) {
                continue;
            }

            $side = $fileData['side'] ?? 'front';
            $hex  = $this->normalizeHex($fileData['color'] ?? '#000000');

            // Delete existing media for this exact side + hex (including model image — will be re-uploaded)
            $mockup->media()
                ->where('collection_name', 'generated_mockups')
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [(string)$template->id])
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.category_id')) = ?", [(string)$mockup->category_id])
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.side')) = ?", [$side])
                ->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex'))) = ?", [$hex])
                ->cursor()
                ->each->delete();

            $customProperties = [
                'side'        => $side,
                'template_id' => (string)$template->id,
                'hex'         => $hex,
                'category_id' => (int)$mockup->category_id,
                'model_image' => ($modelColor && $hex === $modelColor) ? 1 : 0,
            ];

            $mockup->addMedia($request->file("files.{$index}.file"))
                ->usingFileName("mockup_{$side}_tpl{$template->id}_{$hex}.png")
                ->withCustomProperties($customProperties)
                ->toMediaCollection('generated_mockups');
        }
    }

    private function normalizeHex(?string $hex): string
    {
        return strtolower(ltrim(trim((string) $hex), '#'));
    }
    public function setTemplateImage(Template $template, Mockup $mockup, Request $request)
    {
        $request->validate([
            'model_color' => ['required', 'string'],
            'side'        => ['required', 'string', 'in:front,back,none'],
        ]);

        $normalizedColor = $this->normalizeHex($request->model_color);

        $attachedMockupIds = $template->mockups()->pluck('mockups.id')->toArray();

        foreach ($attachedMockupIds as $attachedMockupId) {
            $template->mockups()->updateExistingPivot($attachedMockupId, [
                'model_color' => ((int) $attachedMockupId === (int) $mockup->id)
                    ? $request->model_color
                    : null,
            ]);
        }

        if (!in_array((int) $mockup->id, array_map('intval', $attachedMockupIds), true)) {
            $template->mockups()->attach($mockup->id, [
                'model_color' => $request->model_color,
            ]);
        }

        Media::query()
            ->where('model_type', Mockup::class)
            ->where('collection_name', 'generated_mockups')
            ->where('custom_properties->template_id', (string) $template->id)
            ->get()
            ->each(function (Media $media) {
                $media->setCustomProperty('model_image', 0);
                $media->save();
            });

        $query = Media::query()
            ->where('model_type', Mockup::class)
            ->where('model_id', $mockup->id)
            ->where('collection_name', 'generated_mockups')
            ->where('custom_properties->template_id', (string) $template->id)
            ->where('custom_properties->hex', $normalizedColor);


        $matchedMedia = $query->get();

        if ($matchedMedia->isEmpty() && $request->side !== 'none') {
            $matchedMedia = Media::query()
                ->where('model_type', Mockup::class)
                ->where('model_id', $mockup->id)
                ->where('collection_name', 'generated_mockups')
                ->where('custom_properties->template_id', (string) $template->id)
                ->where('custom_properties->hex', $normalizedColor)
                ->get();
        }

        $matchedMedia->each(function (Media $media) {
            $media->setCustomProperty('model_image', 1);
            $media->save();
        });

        return Response::api();
    }
}
