<?php

namespace App\Services;


use App\Enums\Mockup\TypeEnum;
use App\Jobs\HandleMockupFilesJob;
use App\Models\Mockup;
use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\MockupRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\Mockup\MockupRenderer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class MockupService extends BaseService
{
    public BaseRepositoryInterface $repository;

    public function __construct(MockupRepositoryInterface $repository, public MockupRenderer $renderer
        ,public ProductRepositoryInterface $productRepository
    )
    {
        parent::__construct($repository);
    }

    public function getMockups(): array
    {
        $categoryId  = request('product_id');
        $productType  = request('type');
        $templateId = request('template_id');
        $color      = request('color');
        $categoryId =  $productType == 'category' ? $categoryId : $this->productRepository->query()->whereId($categoryId)->first()?->category_id;

        $mockups = $this->repository
            ->query()
            ->when($categoryId, fn($q) => $q->whereCategoryId($categoryId))
            ->when($templateId, fn($q) => $q->whereHas('templates', function ($query) use ($templateId) {
                $query->where('templates.id', $templateId);
            }))
            ->with([
                'templates:id',
                'types:id,value',
                'media' => fn($q) => $q->whereIn('collection_name', [
                    'mockups',
                    'generated_mockups',
                    'templates',
                    'back_templates'
                ])
            ])
            ->get();

        $colors = $mockups
            ->flatMap(fn ($mockup) => $mockup->templates
                ->filter(fn($template) => $template->id == $templateId)
                ->map(function ($tpl) use ($templateId) {
                    $c = $tpl->pivot->colors ?? [];
                    if (is_string($c)) $c = json_decode($c, true) ?: [];
                    return is_array($c) ? $c : [];

                }))
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->all();

        $requested = $color
            ? (str_starts_with($color, '#') ? strtolower($color) : '#'.strtolower($color))
            : null;

        $media = $mockups
            ->filter(fn ($mockup) => $mockup->templates->contains('id', $templateId))
            ->flatMap(fn ($mockup) =>
            $mockup->media
                ->where('collection_name', 'generated_mockups')
                ->filter(function ($m) use ($colors) {
                    $hex = strtolower($m->getCustomProperty('hex', ''));
                    return $hex && in_array($hex, array_map('strtolower', $colors));
                })
            )
            ->values();


        if ($requested) {
            $media = $media->filter(function ($m) use ($requested) {
                $hex = $m->getCustomProperty('hex');
                return is_string($hex) && strtolower($hex) === $requested;
            });
        }

        $pickBySide = function ($media, string $side): array {
            $items = $media
                ->filter(fn($m) => $m->getCustomProperty('side') === $side)
                ->sortBy('id') // optional: ترتيب ثابت
                ->values();

            if ($items->isEmpty()) return [];

            $modelIds = $items->pluck('model_id')->unique()->values();

            if ($modelIds->count() === 1) {
                return [$items->first()->getFullUrl()];
            }

            $urls = [];
            foreach ($modelIds as $mid) {
                $m = $items->firstWhere('model_id', $mid);
                if ($m) $urls[] = $m->getFullUrl();
            }

            return $urls;
        };

        $front = $pickBySide($media, 'front');
        $back  = $pickBySide($media, 'back');
        $none  = $pickBySide($media, 'none');


        $urls = array_values(array_merge($front, $back, $none));


        return [
            'colors' => $colors,
            'urls'   => $urls,
        ];

    }

    public function getAll(
        $relations = [], bool $paginate = false, $columns = ['*'], $perPage = 16, $counts = [])
    {

        $requested = request('per_page', $perPage);
        $pageSize = $requested === 'all' ? null : (int)$requested;

        $query = $this->repository
            ->query()
            ->with(['category:id,name'])
            ->when(request()->filled('search_value'), function ($q) {
                if (hasMeaningfulSearch(request('search_value'))) {
                    $q->where('name', 'LIKE', '%' . request('search_value') . '%');
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
            ->when(request()->filled('color'), function ($query) {

            })
            ->when(request()->filled('product_id'), fn($q) => $q->whereCategoryId(request('product_id')))
            ->when(request()->filled('template_id'), fn($q) => $q->whereHas('templates', function ($query) {
                $query->where('templates.id', request('template_id'));
            }))
            ->when(request()->filled('product_ids'), fn($q) => $q->whereIn('category_id', request()->array('product_ids')))
            ->when(request()->filled('type'), fn($q) => $q->whereHas('types', fn($q) => $q->where('types.id', request('type'))))
            ->when(request()->filled('types'), fn($q) => $q->whereHas('types', fn($q) => $q->whereIn('types.value', request('types'))))
            ->when(request()->filled('search'), function ($q) {
                $q->where('name', 'like', '%' . request('search') . '%');
            })
            ->latest();

        if (request()->ajax()) {
            return $pageSize === null
                ? $query->get()
                : $query->paginate($pageSize)->withQueryString();
        }
        if (request()->expectsJson()) {
            return $query->paginate($pageSize);
        }
        return $this->repository->all(
            $paginate,
            $columns,
            $relations,
            filters: $this->filters,
            perPage: $pageSize ?? $perPage
        );
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $model = $this->handleTransaction(function () use ($validatedData) {
            $model = $this->repository->create($validatedData);
            $model->types()->attach(Arr::get($validatedData, 'types') ?? []);
                collect($validatedData['templates'])->each(function ($template) use ($model) {
                    $templateId = $template['template_id'] ?? null;
                    if (!$templateId) return;

                    $positions = collect($template)
                        ->except(['template_id', 'colors'])
                        ->filter(fn ($value) => !is_null($value))
                        ->toArray();

                    $colors = Arr::get($template, 'colors', []);

                    $colors = collect($colors)
                        ->filter(fn ($c) => is_string($c) && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $c))
                        ->values()
                        ->all();


                    $model->templates()->syncWithoutDetaching([
                        $templateId => [
                            'positions' => $positions,
                            'colors'    => $colors,
                        ],
                    ]);
                });

            return $model;
        });
        $this->handleFiles($model);
        HandleMockupFilesJob::dispatch($model);
        return $model;
    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        $model = $this->handleTransaction(function () use ($id, $validatedData) {

            $model = $this->repository->update($validatedData, $id);

            // 1️⃣ sync types
            $selectedTypeValues = Arr::get($validatedData, 'types', []);
            $model->types()->sync($selectedTypeValues);

            // 2️⃣ sync templates
            $templatesInput = collect(Arr::get($validatedData, 'templates', []));

            if ($templatesInput->isNotEmpty()) {
                $syncData = [];

                $templatesInput->each(function ($template) use (&$syncData) {
                    $templateId = $template['template_id'] ?? null;
                    if (!$templateId) return;

                    $positions = collect($template)
                        ->except(['template_id', 'colors'])
                        ->filter(fn($v) => $v !== null && $v !== '')
                        ->toArray();

                    $colors = Arr::get($template, 'colors', []);
                    if (is_string($colors)) {
                        $colors = json_decode($colors, true) ?: [];
                    }
                    if (!is_array($colors)) $colors = [];

                    $colors = collect($colors)
                        ->filter(fn($c) => is_string($c) && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $c))
                        ->values()
                        ->all();

                    $syncData[$templateId] = [
                        'positions' => $positions,
                        'colors'    => $colors,
                    ];
                });

                // ✅ update or attach logic
                foreach ($syncData as $templateId => $pivotData) {
                    $existing = $model->templates()->where('template_id', $templateId)->exists();

                    if ($existing) {
                        // update only changed fields (partial update)
                        $currentPivot = $model->templates()->where('template_id', $templateId)->first()?->pivot;

                        $merged = [
                            'positions' => $pivotData['positions'],
                            'colors' => $pivotData['colors'],
                        ];

                        $model->templates()->updateExistingPivot($templateId, $merged);
                    } else {
                        // attach new
                        $model->templates()->attach($templateId, $pivotData);
                    }
                }
            }

            // 3️⃣ handle media uploads
            if (request()->allFiles()) {
                $this->handleFiles($model, true);
            }

            // 4️⃣ reload relations
            $model->load(['templates', 'types', 'category', 'media']);

            // 5️⃣ regenerate mockups
            foreach ($model->templates as $template) {
                $pivotPositions = $template->pivot->positions ?? [];
                $pivotColors    = $template->pivot->colors ?? [];

                if (is_string($pivotColors)) {
                    $pivotColors = json_decode($pivotColors, true) ?: [];
                }

                $colorsToRender = count($pivotColors) ? $pivotColors : [null];

                foreach ($model->types as $type) {
                    $sideName = strtolower($type->value->name);

                    $baseMedia = $model->getMedia('mockups')
                        ->first(fn($m) => $m->getCustomProperty('side') === $sideName && $m->getCustomProperty('role') === 'base');
                    $maskMedia = $model->getMedia('mockups')
                        ->first(fn($m) => $m->getCustomProperty('side') === $sideName && $m->getCustomProperty('role') === 'mask');

                    if (!$baseMedia || !$maskMedia) continue;

                    $designMedia = ($sideName === 'back')
                        ? $template->getFirstMedia('back_templates')
                        : $template->getFirstMedia('templates');

                    if (!$designMedia || !$designMedia->getPath()) continue;

                    [$baseWidth, $baseHeight] = getimagesize($baseMedia->getPath());

                    $xPct  = (float)($pivotPositions["{$sideName}_x"] ?? 0.5);
                    $yPct  = (float)($pivotPositions["{$sideName}_y"] ?? 0.5);
                    $wPct  = (float)($pivotPositions["{$sideName}_width"] ?? 0.4);
                    $hPct  = (float)($pivotPositions["{$sideName}_height"] ?? 0.4);
                    $angle = (float)($pivotPositions["{$sideName}_angle"] ?? 0);

                    $printW = max(1, (int) round($wPct * $baseWidth));
                    $printH = max(1, (int) round($hPct * $baseHeight));

                    $centerX = $xPct * $baseWidth;
                    $centerY = $yPct * $baseHeight;

                    $printX = (int) round($centerX - $printW / 2);
                    $printY = (int) round($centerY - $printH / 2);

                    foreach ($colorsToRender as $hex) {
                        $binary = (new MockupRenderer())->render([
                            'base_path'   => $baseMedia->getPath(),
                            'shirt_path'  => $maskMedia->getPath(),
                            'design_path' => $designMedia->getPath(),
                            'print_x'     => $printX,
                            'print_y'     => $printY,
                            'print_w'     => $printW,
                            'print_h'     => $printH,
                            'angle'       => $angle,
                            'hex'         => $hex,
                        ]);

                        $safeHex = $hex ? ltrim(strtolower($hex), '#') : 'no-color';

                        $model->addMediaFromString($binary)
                            ->usingFileName("mockup_{$sideName}_tpl{$template->id}_{$safeHex}.png")
                            ->withCustomProperties([
                                'side'        => $sideName,
                                'template_id' => $template->id,
                                'hex'         => $hex,
                            ])
                            ->toMediaCollection('generated_mockups');
                    }
                }
            }

            return $model;
        });

        return $model;
    }

    /**
     * @param mixed $model
     * @return mixed
     */
    public function handleFiles(mixed $model, $clearExisting = false): mixed
    {
        if (!request()->allFiles()) {
            return $model;
        }

        $types = collect(request()->input('types', []));
        $mediaTypes = collect(['base_image', 'mask_image']);

        $types->each(function ($type) use ($mediaTypes, $model, $clearExisting) {
            $sideName = strtolower(TypeEnum::from($type)->name);

            $mediaTypes->each(function ($mediaType) use ($sideName, $type, $model, $clearExisting) {
                $inputName = "{$sideName}_{$mediaType}";

                if (!request()->hasFile($inputName)) {
                    return;
                }

                $role = str_contains($mediaType, 'base') ? 'base' : 'mask';

                if ($clearExisting) {
                    $model->media()
                        ->where('collection_name', 'mockups')
                        ->where('custom_properties->side', $sideName)
                        ->where('custom_properties->role', $role)
                        ->delete();
                }
                handleMediaUploads(
                    request()->file($inputName),
                    $model,
                    customProperties: [
                        'side' => $sideName,
                        'role' => $role,
                    ],

                );
            });
        });

        return $model;
    }

    public function deleteResource($id)
    {
        $model = $this->repository->find($id);
        if ($model->hasMedia()) {
            clearMediaCollections($model);
        }
        $model->types()->newPivotStatement()
            ->where('typeable_id', (string)$model->id)
            ->where('typeable_type', get_class($model))
            ->delete();

        return $model->delete();
    }

    public function bulkDeleteResources($ids)
    {
        return $this->handleTransaction(function () use ($ids) {
            $models = $this->repository->query()->whereIn('id', $ids)->get();

            $models->each(function ($model) {

                $model->types()->detach();

                if ($model->hasMedia()) {
                    clearMediaCollections($model);
                }
            });
            return $this->repository->query()->whereIn('id', $ids)->delete();
        });


    }

    public function showAndUpdateRecent($id)
    {
        $mockup = $this->repository->find($id);
        return $mockup->load('types');
//        return auth('web')->user()->recentMockups()->syncWithoutDetaching([$mockup->id]);
    }

    public function destroyRecentMockup($id)
    {
        return auth('web')->user()->recentMockups()->detach($id);
    }

    public function recentMockups()
    {
        return auth('web')->user()->recentMockups()->take(5)->get();
    }
}
