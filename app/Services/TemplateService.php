<?php

namespace App\Services;


use App\Enums\OrientationEnum;
use App\Enums\Template\StatusEnum;
use App\Enums\Template\TypeEnum;
use App\Jobs\HandleMockupFilesJob;
use App\Jobs\ProcessBase64Image;
use App\Models\Admin;
use App\Models\FontStyle;
use App\Models\Mockup;
use App\Models\Template;
use App\Models\Type;
use App\Repositories\Base\BaseRepositoryInterface;
use App\Traits\RendersTemplateMockups;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Repositories\Interfaces\{CategoryRepositoryInterface, ProductRepositoryInterface, TemplateRepositoryInterface};
use Maatwebsite\Excel\Facades\Excel;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use ZipArchive;

class TemplateService extends BaseService
{
    use RendersTemplateMockups;

    public BaseRepositoryInterface $repository;

    public function __construct(TemplateRepositoryInterface $repository
        , public ProductRepositoryInterface                 $productRepository
        , public CategoryRepositoryInterface                $categoryRepository
        , public ImageService                               $imageService
    )
    {
        parent::__construct($repository);

    }

    public function getAll(
        $relations = [], bool $paginate = false, $columns = ['*'], $perPage = 16, $counts = [])
    {
        request('with_design_data', true);


        $requested = request('per_page', $perPage);
        $pageSize = $requested === 'all' ? null : (int)$requested;

        $productId = request('product_id');
        $categoryId = request('product_without_category_id');
        $locale = app()->getLocale();
        $search = request('search');
        $query = $this->repository
            ->query()->with([
                'products:id,name',
                'tags' => function ($q) use ($locale, $search) {

                    if ($search !== '') {
                        $q->whereRaw(
                            "LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?",
                            ["%{$search}%"]
                        );
                    }
                },
                'types',
            ])->when(request()->filled('search_value'), function ($q) use ($locale) {
                if (hasMeaningfulSearch(request('search_value'))) {

                    $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?", [
                        '%' . strtolower(request('search_value')) . '%'
                    ]);
                } else {
                    $q->whereRaw('1 = 0');
                }
            })->when(filter_var(request('has_not_mockups'), FILTER_VALIDATE_BOOLEAN), function ($q) {
                if (request('mockup_id')) {
                    $q->whereDoesntHave('mockups', function ($q) {
                        $q->where('id', request('mockup_id'));
                    });
                } else {
                    $q->whereDoesntHave('mockups', function ($q) {
                        $q->where('category_id', request('product_without_category_id'));
                    });

                }

            })
            ->when(request()->filled('types'), function ($query) {
                $types = array_map('intval', request()->input('types'));
                $query->whereHas('types', function ($q) use ($types) {
                    $q->whereIn('types.value', $types);
                }, '=', count($types));

                $query->whereDoesntHave('types', function ($q) use ($types) {
                    $q->whereNotIn('types.value', $types);
                });
            })
            ->when(request()->filled('product_id'), function ($query) use ($productId) {
                $query->whereHas('products', function ($q) use ($productId) {
                    $q->where('products.id', $productId);
                });
            })
            ->when(request('product_without_category_id'), function ($q) use ($categoryId) {
                $q->where(function ($q) use ($categoryId) {
                    $q->whereHas('categories', function ($q) use ($categoryId) {
                        $q->where('categories.id', $categoryId);
                    })
                        ->orwhereHas('products.category', function ($q) use ($categoryId) {
                            $q->where('categories.id', $categoryId);
                        })->orwhereHas('products', function ($q) use ($categoryId) {
                            $category = $this->categoryRepository->find($categoryId);
                            $q->whereIn('products.id', $category->products->pluck('id'));
                        });
                });

            })
            ->when(request()->filled('approach'), function ($q) {
                $q->where('approach', request('approach'));
            })->when(request()->filled('order'), function ($q) {
                $q->orderBy('order', request('order'));
            })
            ->when(request('category_id'), function ($q) {
                $q->whereHas('products', function ($q) {
                    $q->whereCategoryId(request('category_id'));
                });
            })
            ->when(request('search'), function ($q) use ($locale) {
                $q->whereHas('tags', function ($q) use ($locale) {
                    $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?", [
                        '%' . strtolower(request('search')) . '%'
                    ]);
                });
            })
            ->when(request()->filled('status'), fn($q) => $q->whereStatus(request('status')))
            ->when(request()->filled('best_seller'), fn($q) => $q->where('is_best_seller', request('best_seller')))
            ->when(request()->filled('is_landing'), function ($query) {
                $query->where('is_landing', true);
            })->when(request()->filled('tags'), function ($q) {
                $tags = request('tags');
                $q->whereHas('tags', function ($q) use ($tags) {
                    $q->whereIn('tags.id', is_array($tags) ? $tags : [$tags]);
                });
            })->when(request()->filled('industries'), function ($q) {
                $industries = request('industries');
                $q->whereHas('industries', function ($q) use ($industries) {
                    $q->whereIn('industries.id', is_array($industries) ? $industries : [$industries]);
                });
            })
            ->when(request()->filled('orientation'), function ($q) {
                $q->whereOrientation(OrientationEnum::tryFrom(request('orientation')));
            })
            ->when(request()->filled('limit'), function ($q) {
                $q->limit((int)request('limit'));
            })->when(request()->filled('languages'), function ($q) {
                $languages = request('languages');
                $languages = is_array($languages) ? $languages : [$languages];
                $q->where(function ($qq) use ($languages) {
                    foreach ($languages as $lang) {
                        $qq->orWhereJsonContains('supported_languages', $lang);
                    }
                });
            });

        if (request()->ajax()) {
            return $pageSize === null
                ? $query->latest()->get()
                : $query->latest()->paginate($pageSize)->withQueryString();
        }

        if (request()->expectsJson()) {

            $query = $query
                ->whereStatus(StatusEnum::LIVE)
                ->where(function ($q) {
                    $q->where('approach', '!=', 'without_editor')
                        ->orWhere(function ($q) {
                            $q->where('approach', 'without_editor')
                                ->whereHas('mockups', function ($q) {
                                    $categoryId =
                                        request('product_without_category_id')
                                        ?? $this->productRepository->query()->find(request('product_id'))?->category_id;

                                    $q->where('mockups.category_id', $categoryId)
                                        ->where('mockup_template.colors', '!=', '[]');
                                });

                        });
                })->orderByDesc('is_best_seller')
                ->latest();


            return $paginate
                ? $query->paginate($requested)
                : $query->get();
        }

        return $paginate
            ? $query->latest()->paginate($requested)
            : $query->latest()->get();
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $colors = Arr::get($validatedData, 'colors');
        $finalColors = collect($colors)->flatMap(function ($color) {
            return [
                $color['value'],
            ];
        })->toArray();
        $validatedData['colors'] = $finalColors;
        $model = $this->handleTransaction(function () use ($validatedData, $relationsToStore, $relationsToLoad, $colors) {
            $model = $this->repository->create($validatedData);
            $model->products()->sync($validatedData['product_ids'] ?? []);
            $model->industries()->sync($validatedData['industry_ids'] ?? []);
            $model->categories()->sync($validatedData['category_ids'] ?? []);
            if (request()->allFiles()) {
                handleMediaUploads(request()->allFiles(), $model);
            }

            if (isset($validatedData['template_image_id'])) {
                Media::where('id', $validatedData['template_image_id'])
                    ->update([
                        'model_type' => get_class($model),
                        'model_id' => $model->id,
                        'collection_name' => 'template_model_image',
                    ]);
            }
            if (isset($validatedData['template_image_front_id']) || isset($validatedData['template_image_none_id'])) {
                Media::where(function ($query) use ($validatedData) {
                    $query->whereKey($validatedData['template_image_front_id'])
                        ->orWhere('id', $validatedData['template_image_none_id']);
                })
                    ->update([
                        'model_type' => get_class($model),
                        'model_id' => $model->id,
                        'collection_name' => 'templates',
                    ]);
                $this->imageService->processUploaded($validatedData['template_image_front_id'] ?? $validatedData['template_image_none_id']);

            }
            if (isset($validatedData['template_image_back_id'])) {

                Media::whereKey($validatedData['template_image_back_id'])
                    ->update([
                        'model_type' => get_class($model),
                        'model_id' => $model->id,
                        'collection_name' => 'back_templates',
                    ]);
                $this->imageService->processUploaded($validatedData['template_image_back_id'],'back-templates');

            }
            $mockupIds = $validatedData['mockup_ids'] ?? [];
            $selectedTypeValues = Arr::get($validatedData, 'types', []);
            $model->types()->sync($validatedData['types']);

            if (!empty($mockupIds)) {
                $positions = $this->defaultPositionsForTypes($selectedTypeValues);

                $pivotData = collect($mockupIds)->mapWithKeys(function ($mockupId) use ($positions) {
                    return [
                        (int)$mockupId => [
                            'positions' => $positions,
                            'colors' => ['#000000', '#ffffff']
                        ],
                    ];
                })->toArray();

                $model->mockups()->syncWithoutDetaching($pivotData);
                foreach ($mockupIds as $mockupId) {
                    $mockup = Mockup::find((int)$mockupId);
                    if (!$mockup) continue;
                    HandleMockupFilesJob::dispatch($mockup);
                }


            }
            $model->types->each(function ($type) use ($model) {
                $side = strtolower($type->value->name);
                $collection = match ($side) {
                    'back' => 'back_templates',
                    default => 'templates',
                };
                $media = $model->getFirstMedia($collection);
                if (!$media || !file_exists($media->getPath())) return;
                $this->renderMockups($model, $collection);
            });
            $model->tags()->sync($validatedData['tags'] ?? []);
            $model->flags()->sync($validatedData['flags'] ?? []);
            collect($colors)->each(function ($color) use ($model) {
                if (empty($color['image_id'])) {
                    return;
                }

                $media = Media::where('id', $color['image_id'])->first();

                if ($media) {
                    $media->update([
                        'model_type' => get_class($model),
                        'model_id' => $model->id,
                        'collection_name' => 'color_templates',
                    ]);

                    $media->setCustomProperty('color_hex', $color['value']);
                    $media->save();
                }
            });
            return $model->refresh();
        });


        if (isset($validatedData['base64_preview_image'])) {
            ProcessBase64Image::dispatch($validatedData['base64_preview_image'], $model, 'templates');
        }
        if (isset($validatedData['back_base64_preview_image'])) {
            ProcessBase64Image::dispatch($validatedData['back_base64_preview_image'], $model, 'back_templates');
        }

        return $model->load($relationsToLoad);
    }

    private function defaultPositionsForTypes(array $typeIds): array
    {
        $sides = [];

        if (in_array(1, $typeIds, true)) $sides[] = 'front';
        if (in_array(2, $typeIds, true)) $sides[] = 'back';
        if (in_array(3, $typeIds, true)) $sides[] = 'none';

        if (!$sides) $sides = ['front', 'back', 'none'];

        $pos = [];
        foreach ($sides as $side) {
            $pos["{$side}_x"] = 0.5;
            $pos["{$side}_y"] = 0.507031;
            $pos["{$side}_width"] = 0.264844;
            $pos["{$side}_height"] = 0.176563;
            $pos["{$side}_angle"] = 0;
        }

        return $pos;
    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        if (empty($validatedData['supported_languages'])) {
            $validatedData['supported_languages'] = null;
        }

        $colors = Arr::get($validatedData, 'colors');
        $finalColors = collect($colors)->flatMap(fn($color) => [$color['value']])->toArray();
        $validatedData['colors'] = $finalColors;

        $model = $this->handleTransaction(function () use ($validatedData, $id, $colors) {
            $model = $this->repository->update($validatedData, $id);

            $selectedTypeValues = Arr::get($validatedData, 'types', []);
            $modelTypesValues = $model->types->pluck('value.value')->toArray();

            $typesChanged = collect($selectedTypeValues)->sort()->values()->all()
                != collect($modelTypesValues)->sort()->values()->all();

            if ($typesChanged) {
                $hasFront = in_array(1, $selectedTypeValues);
                $hasBack = in_array(2, $selectedTypeValues);
                $hasNone = in_array(3, $selectedTypeValues);

                if (!($hasFront && $hasBack)) {
                    if ($hasFront) {
                        $model->clearMediaCollection('back_templates');
                        $model->clearMediaCollection('back-templates-preview');
                    }
                    if ($hasBack) {
                        $model->clearMediaCollection('templates');
                        $model->clearMediaCollection('templates-preview');
                    }

                    if ($hasNone) {
                        $model->clearMediaCollection('templates');
                        $model->clearMediaCollection('templates-preview');
                        $model->clearMediaCollection('back_templates');
                        $model->clearMediaCollection('back-templates-preview');
                    }
                }
            }

            $model->types()->sync($selectedTypeValues);
            $model->industries()->sync($validatedData['industry_ids'] ?? []);

            if (isset($validatedData['template_image_id'])) {
                $model->getMedia('template_model_image')
                    ->where('id', '!=', $validatedData['template_image_id'])
                    ->each->delete();

                Media::where('id', $validatedData['template_image_id'])->update([
                    'model_type' => get_class($model),
                    'model_id' => $model->id,
                    'collection_name' => 'template_model_image',
                ]);
            }

            if (isset($validatedData['template_image_front_id']) || isset($validatedData['template_image_none_id'])) {
                $model->getMedia('templates')
                    ->where(function ($query) use ($validatedData) {
                        $query->where('id', '!=', $validatedData['template_image_front_id'])
                            ->orWhere('id', '!=', $validatedData['template_image_none_id']);
                    })
                    ->each->delete();

                Media::where(function ($query) use ($validatedData) {
                    $query->whereKey($validatedData['template_image_front_id'])
                        ->orWhere('id', $validatedData['template_image_none_id']);
                })->update([
                    'model_type' => get_class($model),
                    'model_id' => $model->id,
                    'collection_name' => 'templates',
                ]);
                $this->imageService->processUploaded($validatedData['template_image_front_id'] ?? $validatedData['template_image_none_id']);

            }

            if (isset($validatedData['template_image_back_id'])) {
                $model->getMedia('back_templates')
                    ->where('id', '!=', $validatedData['template_image_back_id'])
                    ->each->delete();

                Media::whereKey($validatedData['template_image_back_id'])->update([
                    'model_type' => get_class($model),
                    'model_id' => $model->id,
                    'collection_name' => 'back_templates',
                ]);
                $this->imageService->processUploaded($validatedData['template_image_back_id'],'back-templates');
            }

            $mockupIds = collect($validatedData['mockup_ids'] ?? [])->map(fn($id) => (int)$id);
            $existingMockupIds = $model->mockups->pluck('id');

            $newMockupIds = $mockupIds->diff($existingMockupIds);
            $removedMockupIds = $existingMockupIds->diff($mockupIds);

            if ($mockupIds->isNotEmpty()) {
                $positions = $this->defaultPositionsForTypes($selectedTypeValues);

                $pivotData = $mockupIds->mapWithKeys(function ($mockupId) use ($positions, $model) {
                    $existingPivot = $model->mockups->firstWhere('id', $mockupId);
                    $existingColors = $existingPivot?->pivot->colors ?? null;

                    if ($existingPivot && !empty($existingColors)) {
                        return [
                            $mockupId => [
                                'positions' => $positions,
                                'colors' => $existingColors,
                            ],
                        ];
                    }

                    $mockup = Mockup::find($mockupId);
                    $mockupColors = $mockup?->colors;

                    return [
                        $mockupId => [
                            'positions' => $positions,
                            'colors' => !empty($mockupColors) ? $mockupColors : ['#000000', '#ffffff'],
                        ],
                    ];
                })->toArray();
                $model->mockups()->sync($pivotData);

                $model->types->each(function ($type) use ($model) {
                    $side = strtolower($type->value->name);
                    $collection = $side === 'back' ? 'back_templates' : 'templates';
                    $media = $model->getFirstMedia($collection);
                    if (!$media || !file_exists($media->getPath())) return;
                    $this->renderMockups($model, $collection);
                });

                foreach ($newMockupIds as $mockupId) {
                    $mockup = Mockup::find($mockupId);
                    if (!$mockup) continue;
                    HandleMockupFilesJob::dispatch($mockup, 'create');
                }

                if ($typesChanged) {
                    foreach ($mockupIds->diff($newMockupIds) as $mockupId) {
                        $mockup = Mockup::find($mockupId);
                        if (!$mockup) continue;
                        HandleMockupFilesJob::dispatch($mockup, 'update');
                    }
                }

            } else {
                $model->mockups()->detach();
            }

            foreach ($removedMockupIds as $removedId) {
                $removedMockup = Mockup::find($removedId);
                if (!$removedMockup) continue;

                $removedMockup->getMedia('generated_mockups')
                    ->filter(fn($m) => $m->getCustomProperty('template_id') === $model->id)
                    ->each->delete();
            }

            $model->products()->sync($validatedData['product_ids'] ?? []);
            $model->categories()->sync($validatedData['category_ids'] ?? []);
            $model->tags()->sync($validatedData['tags'] ?? []);
            $model->flags()->sync($validatedData['flags'] ?? []);

            $imageIds = collect($colors)->pluck('image_id')->filter()->toArray();

            $model->getMedia('color_templates')
                ->whereNotIn('id', $imageIds)
                ->each->delete();

            collect($colors)->each(function ($color) use ($model) {
                if (empty($color['image_id'])) return;

                $media = Media::where('id', $color['image_id'])->first();
                if ($media) {
                    $media->update([
                        'model_type' => get_class($model),
                        'model_id' => $model->id,
                        'collection_name' => 'color_templates',
                    ]);
                    $media->setCustomProperty('color_hex', $color['value']);
                    $media->save();
                }
            });


            return $model;
        });

        if (request()->allFiles()) {
            handleMediaUploads(request()->allFiles(), $model, clearExisting: true);
        }

        return $model->load($relationsToLoad);
    }

    public function updateEditorData($validatedData, $id, $relationsToLoad = [])
    {
        $model = $this->repository->update($validatedData, $id);
        $fontStyleIds = collect($validatedData['font_styles_ids'] ?? [])
            ->filter()
            ->unique()
            ->values();

        if ($fontStyleIds->isNotEmpty()) {
            $fontMediaIds = FontStyle::query()
                ->whereIn('id', $fontStyleIds)
                ->with('media')
                ->get()
                ->map(function ($fontStyle) {
                    return optional($fontStyle->media->first())->id;
                })
                ->filter()
                ->unique()
                ->values();

            $existingFontMediaIds = $model->libraryMedia()
                ->wherePivot('type', 'font')
                ->pluck('media.id')
                ->toArray();

            $toDetach = array_diff($existingFontMediaIds, $fontMediaIds->toArray());
            if (!empty($toDetach)) {
                $model->libraryMedia()->detach($toDetach);
            }

            $syncFontData = $fontMediaIds->mapWithKeys(function ($mediaId) {
                return [
                    $mediaId => ['type' => 'font'],
                ];
            })->toArray();

            $model->libraryMedia()->syncWithoutDetaching($syncFontData);
        } else {
            $existingFontMediaIds = $model->libraryMedia()
                ->wherePivot('type', 'font')
                ->pluck('media.id')
                ->toArray();

            if (!empty($existingFontMediaIds)) {
                $model->libraryMedia()->detach($existingFontMediaIds);
            }
        }
        if (isset($validatedData['base64_preview_image'])) {
            ProcessBase64Image::dispatch($validatedData['base64_preview_image'], $model, 'templates');
        }
        if (isset($validatedData['back_base64_preview_image'])) {
            ProcessBase64Image::dispatch($validatedData['back_base64_preview_image'], $model, 'back_templates');
        }
        if (request()->allFiles()) {
            handleMediaUploads(request()->allFiles(), $model, clearExisting: true);
        }

        return $model->load($relationsToLoad);
    }

    public function getProductTemplates($productId)
    {
        $search = trim(request()->input('search'));
        $tags = array_filter((array)request()->input('tags'));
        $types = array_filter((array)request()->input('types'));
        $recent = request()->boolean('recent');

        return $this->repository->query()
            ->with(['media', 'products', 'types'])
            ->when($search, function ($query) use ($search) {
                $locale = app()->getLocale();
                $query->where("name->{$locale}", 'LIKE', "%{$search}%");
            })
            ->when(!empty($tags), function ($query) use ($tags) {
                $query->whereHas('tags', function ($q) use ($tags) {
                    $q->whereIn('tags.id', $tags);
                });
            })
            ->when(request()->filled('approach'), function ($q) {
                $q->where('approach', request('approach'));
            })
            ->when(!empty($types), function ($query) use ($types) {
                $query->whereHas('types', function ($q) use ($types) {
                    $q->whereIn('types.id', $types);
                });
            })
            ->when($recent === true, function ($query) {
                $query->whereNotNull('updated_at')
                    ->orderByDesc('updated_at')
                    ->take(10);
            }, function ($query) {
                $query->oldest();
            })
            ->when(!is_null($productId), function ($query) use ($productId) {
                $query->whereHas('products', function ($q) use ($productId) {
                    $q->where('products.id', $productId);
                });
            })->orderByDesc('is_best_seller')
            ->latest()
            ->paginate(10);
    }


    public function templateAssets()
    {
        $notAuth = request()->is('api/v1/admin/*');
        $model = $notAuth ? Admin::first() : getAuthOrGuest();
        return Media::query()
            ->whereMorphedTo('model', $model)
            ->whereCollectionName("template_assets")
            ->latest()
            ->paginate();
    }

    public function storeTemplateAssets($request)
    {
        $validated = $request->validate(["file" => "required|file|mimes:svg"]);
        $notAuth = request()->is('api/v1/admin/*');
        $model = $notAuth ? Admin::first() : getAuthOrGuest();
        return handleMediaUploads($validated['file'], $model, "template_assets");

    }

    public function search($request)
    {
        $locale = App::getLocale();
        return $this->repository->query()
            ->when($request->filled('search'), function ($query) use ($request, $locale) {
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?", [
                    '%' . strtolower($request->search) . '%'
                ]);
            })->get();
    }

    public function addToLanding($templateId)
    {
        if ($this->repository->query()->isLanding()->count() == 8) {
            throw ValidationException::withMessages([
                'design_id' => ['you can\'t add more than 8 items.']
            ]);
        }
        $template = $this->repository->find($templateId);
        return tap($template, function ($template) {
            $template->update(['is_landing' => true]);
        });
    }

    public function removeFromLanding($templateId)
    {
        $template = $this->repository->find($templateId);
        return tap($template, function ($template) {
            $template->update(['is_landing' => false]);
        });
    }


    public function importExcelFromPaths(string $excelAbs, string $zipAbs, string $batch): array
    {
        $created = 0;
        $skipped = [];

        // ✅ اقرأ من path
        $sheets = Excel::toArray([], $excelAbs);
        $rows = $sheets[0] ?? [];

        if (count($rows) < 2) {
            return [
                'batch' => $batch,
                'created' => 0,
                'skipped_count' => 1,
                'skipped' => ['Excel/CSV is empty'],
            ];
        }


        $headers = array_map(fn($h) => strtolower(trim((string)$h)), $rows[0]);
        $required = ['name_en', 'name_ar', 'image', 'type', 'model_image'];

        $missing = array_values(array_diff($required, $headers));
        if ($missing) {
            return [
                'batch' => $batch,
                'created' => 0,
                'skipped_count' => 1,
                'skipped' => ['Missing headers: ' . implode(', ', $missing)],
                'found_headers' => $headers,
            ];
        }

        $idx = array_flip($headers);

        // 2) Unzip -> tmp
        $tmpDir = storage_path("app/tmp/import/$batch");
        if (!is_dir($tmpDir)) mkdir($tmpDir, 0775, true);

        $zip = new \ZipArchive();
        if ($zip->open($zipAbs) !== true) {
            return [
                'batch' => $batch,
                'created' => 0,
                'skipped_count' => 1,
                'skipped' => ['Invalid ZIP file'],
            ];
        }
        $zip->extractTo($tmpDir);
        $zip->close();

        // 3) Index images in zip (recursive)
        $allowedExt = ['png', 'jpg', 'jpeg', 'webp'];
        $filesIndex = []; // filename => fullpath

        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tmpDir));
        foreach ($rii as $file) {
            if ($file->isDir()) continue;
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, $allowedExt)) continue;

            $filesIndex[strtolower($file->getBasename())] = $file->getPathname();
        }

        // 4) Maps
        $collectionMap = [
            'front' => 'templates',
            'back' => 'back_templates',
            'none' => 'templates',
        ];

        // ids in your types table
        $valueToId = Type::query()
            ->whereIn('value', TypeEnum::values())
            ->pluck('id', 'value')   // [value => id]
            ->toArray();


        $typeIdMap = [];
        foreach (TypeEnum::cases() as $e) {
            $typeIdMap[$e->key()] = $valueToId[$e->value] ?? null;
        }


        $typeIdMap = array_filter($typeIdMap);

        // 5) Staging dir (avoid "file does not exist")
        $stagingDir = storage_path("app/import_staging/$batch");
        if (!is_dir($stagingDir)) mkdir($stagingDir, 0775, true);

        // 6) Loop rows
        foreach (array_slice($rows, 1) as $r => $row) {
            $rowNum = $r + 2;

            $nameEn = trim((string)($row[$idx['name_en']] ?? ''));
            $nameAR = trim((string)($row[$idx['name_ar']] ?? ''));
            if ($nameEn === '') {
                $skipped[] = "Row $rowNum: missing name";
                continue;
            }

            // ✅ images first
            $imageCells = array_values(array_filter(array_map(
                fn($v) => strtolower(trim((string)$v)),
                explode(',', (string)($row[$idx['image']] ?? ''))
            )));

            if (empty($imageCells)) {
                $skipped[] = "Row $rowNum: missing image";
                continue;
            }

            // ✅ types (FIXED: read BEFORE using)
            $typeCells = array_values(array_filter(array_map(
                fn($v) => strtolower(trim((string)$v)),
                explode(',', (string)($row[$idx['type']] ?? ''))
            )));

            if (empty($typeCells)) {
                $typeCells = ['none'];
            }

            // ✅ pair by index
            $pairCount = min(count($imageCells), count($typeCells));
            if ($pairCount === 0) {
                $skipped[] = "Row $rowNum: missing image/type";
                continue;
            }

            // Create template
            $template = Template::create(['name' => [
                'ar' => $nameAR,
                'en' => $nameEn,
            ], 'approach' => 'without_editor',]);
            $modelCollection = 'template_model_image';
            $modelImage = strtolower(trim((string)($row[$idx['model_image']] ?? '')));

            if ($modelImage !== '') {
                $modelBase = strtolower(basename(str_replace('\\', '/', $modelImage)));
                $srcModel = $filesIndex[$modelBase] ?? null;

                if (!$srcModel || !file_exists($srcModel)) {
                    $skipped[] = "Row $rowNum: model_image not found in zip ($modelBase)";
                } else {
                    $dstModel = $stagingDir . DIRECTORY_SEPARATOR . ('model_' . $modelBase);
                    @copy($srcModel, $dstModel);

                    if (!file_exists($dstModel)) {
                        $skipped[] = "Row $rowNum: failed to stage model_image ($modelBase)";
                    } else {
                        $template->addMedia($dstModel)
                            ->usingFileName($modelBase)
                            ->toMediaCollection($modelCollection);
                    }
                }
            }

            // Attach types (typeables table)
            $typeIds = array_values(array_unique(array_filter(array_map(
                fn($t) => $typeIdMap[$t] ?? null,
                $typeCells
            ))));


            if (!empty($typeIds)) {
                $template->types()->sync($typeIds);
            }

            // Add media per (image,type) pair
            for ($i = 0; $i < $pairCount; $i++) {
                $img = $imageCells[$i];
                $typeKey = $typeCells[$i];

                $imgBase = strtolower(basename(str_replace('\\', '/', $img)));

                $src = $filesIndex[$imgBase] ?? null;
                if (!$src || !file_exists($src)) {
                    $skipped[] = "Row $rowNum: image not found in zip ($imgBase)";
                    continue;
                }

                $collection = $collectionMap[$typeKey] ?? null;
                if (!$collection) {
                    $skipped[] = "Row $rowNum: invalid type ($typeKey)";
                    continue;
                }

                // copy to staging
                $dst = $stagingDir . DIRECTORY_SEPARATOR . $imgBase;
                @copy($src, $dst);

                if (!file_exists($dst)) {
                    $skipped[] = "Row $rowNum: failed to stage file ($imgBase)";
                    continue;
                }

                $template->addMedia($dst)
                    ->usingFileName($imgBase)
                    ->toMediaCollection($collection);
            }

            // mismatch report (optional)
            if (count($imageCells) !== count($typeCells)) {
                $skipped[] = "Row $rowNum: count mismatch images("
                    . count($imageCells) . ") types(" . count($typeCells) . ")";
            }

            $created++;
        }

        // 7) Cleanup
        File::deleteDirectory($tmpDir);
        File::deleteDirectory($stagingDir);

        return [
            'batch' => $batch,
            'created' => $created,
            'skipped_count' => count($skipped),
            'skipped' => $skipped,
        ];
    }
}
