<?php

namespace App\Services;


use App\Enums\OrientationEnum;
use App\Enums\Template\StatusEnum;
use App\Enums\Template\TypeEnum;
use App\Jobs\ProcessBase64Image;
use App\Models\Admin;
use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use App\Repositories\Interfaces\{CategoryRepositoryInterface, ProductRepositoryInterface, TemplateRepositoryInterface};
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TemplateService extends BaseService
{
    public BaseRepositoryInterface $repository;

    public function __construct(TemplateRepositoryInterface $repository
        , public ProductRepositoryInterface                 $productRepository
        , public CategoryRepositoryInterface                $categoryRepository
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
                $q->whereDoesntHave('mockups');
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
            })->when(request('product_without_category_id'), function ($q) use ($categoryId) {
                $q->where(function ($q) use ($categoryId) {
                    $q->whereHas('categories', function ($q) use ($categoryId) {
                        $q->where('categories.id', $categoryId);
                    })
                        ->orwhereHas('products.category', function ($q) use ($categoryId) {
                            $q->where('categories.id', $categoryId);
                        })->orwhereHas('products', function ($q) use ($categoryId) {
                            $category =  $this->categoryRepository->find($categoryId);
                            $q->whereIn('products.id', $category->products->pluck('id'));
                        });
                });

            })
            ->when(request()->filled('approach'), function ($q) {
                $q->where('approach', request('approach'));
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
            })->latest()
            ->when(request()->filled('limit'), function ($q) {
                $q->limit((int) request('limit'));
            });

        if (request()->ajax()) {
            return $pageSize === null
                ? $query->get()
                : $query->paginate($pageSize)->withQueryString();
        }

        if (request()->expectsJson()) {

            $query = $query
                ->whereStatus(StatusEnum::LIVE)
                ->where(function ($q) {
                    $q->where('approach', '!=', 'without_editor') // all others always allowed
                    ->orWhere(function ($q) {
                        $q->where('approach', 'without_editor')

                            ->whereHas('mockups'); // only if has mockups
                    });
                });


            return $paginate
                ? $query->paginate($requested)
                : $query->get();
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
            if ($validatedData['mockup_id']){
                $model->mockups()->syncWithPivotValues(
                    [$validatedData['mockup_id']] ?? [],
                    ['positions' => []]
                );
            }

            $model->types()->sync($validatedData['types']);
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

        if (request()->allFiles()) {
            handleMediaUploads(request()->allFiles(), $model);
        }
        if (isset($validatedData['base64_preview_image'])) {
            ProcessBase64Image::dispatch($validatedData['base64_preview_image'], $model, 'templates');
        }
        if (isset($validatedData['back_base64_preview_image'])) {
            ProcessBase64Image::dispatch($validatedData['back_base64_preview_image'], $model, 'back_templates');
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
        }
        if (isset($validatedData['template_image_back_id'])) {

            Media::whereKey($validatedData['template_image_back_id'])
                ->update([
                    'model_type' => get_class($model),
                    'model_id' => $model->id,
                    'collection_name' => 'back_templates',
                ]);
        }
        return $model->load($relationsToLoad);
    }


    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        $colors = Arr::get($validatedData, 'colors');
        $finalColors = collect($colors)->flatMap(function ($color) {
            return [
                $color['value'],
            ];
        })->toArray();
        $validatedData['colors'] = $finalColors;
        $model = $this->handleTransaction(function () use ($validatedData, $id, $colors) {
            $model = $this->repository->update($validatedData, $id);

            $selectedTypeValues = Arr::get($validatedData, 'types', []);
            $modelTypesValues   = $model->types->pluck('value.value')->toArray();

            $typesChanged = collect($selectedTypeValues)->sort()->values()->all()
                != collect($modelTypesValues)->sort()->values()->all();

            if ($typesChanged) {
                if (in_array(1, $selectedTypeValues) || in_array(3, $selectedTypeValues)) {
                    $model->clearMediaCollection('templates');
                }if (in_array(2, $selectedTypeValues) ) {
                    $model->clearMediaCollection('back_templates');
                }
            }

            $model->types()->sync($validatedData['types']);

            $model->industries()->sync($validatedData['industry_ids'] ?? []);
            $model->products()->sync($validatedData['product_ids'] ?? []);
            $model->categories()->sync($validatedData['category_ids'] ?? []);
            $model->tags()->sync($validatedData['tags'] ?? []);
            $model->flags()->sync($validatedData['flags'] ?? []);

            if (!empty($validatedData['template_image_id'])) {
                $model->getMedia('template_model_image')
                    ->where('id', '!=', $validatedData['template_image_id'])
                    ->each->delete();

                Media::where('id', $validatedData['template_image_id'])
                    ->update([
                        'model_type' => get_class($model),
                        'model_id' => $model->id,
                        'collection_name' => 'template_model_image',
                    ]);
            }
            if (!empty($validatedData['template_image_main_id'])) {
                $model->getMedia('templates')
                    ->where('id', '!=', $validatedData['template_image_main_id'])
                    ->each->delete();

                Media::where('id', $validatedData['template_image_main_id'])
                    ->update([
                        'model_type' => get_class($model),
                        'model_id' => $model->id,
                        'collection_name' => 'templates',
                    ]);
            }

            $imageIds = collect($colors)
                ->pluck('image_id')
                ->filter()
                ->toArray();

            $model->getMedia('color_templates')
                ->whereNotIn('id', $imageIds)
                ->each
                ->delete();
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


            return $model;
        });
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

    public function updateEditorData($validatedData, $id, $relationsToLoad = [])
    {
        $model = $this->repository->update($validatedData, $id);

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
            })
            ->paginate(10);
    }


    public function templateAssets()
    {
        return Media::query()
//            ->whereMorphedTo('model',auth($this->activeGuard)->user())
            ->whereCollectionName("template_assets")
            ->latest()
            ->paginate();
    }

    public function storeTemplateAssets($request)
    {
        $validated = $request->validate(["file" => "required|file|mimes:svg"]);
        return handleMediaUploads($validated['file'], Admin::find(1) ?? Admin::find(7), "template_assets");
//        return handleMediaUploads($validated['file'],auth(getActiveGuard())->user(),"template_assets");

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

}
