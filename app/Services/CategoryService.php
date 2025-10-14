<?php

namespace App\Services;


use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\DimensionRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Yajra\DataTables\DataTables;

class CategoryService extends BaseService
{
    public function __construct(CategoryRepositoryInterface         $repository,
                                public DimensionRepositoryInterface $dimensionRepository,)
    {
        parent::__construct($repository);
    }

    public function getAll($relations = [], bool $paginate = false, $columns = ['*'], $perPage = 10, $counts = [])
    {
        $query = $this->repository->query()
            ->withLastOfferId()
            ->with($relations)
            ->whereNull('parent_id')
            ->when(request()->filled('is_landing'), function ($query) {
                $query->where('is_landing', true);
            })
            ->when(request()->filled('is_has_category'), function ($query) {
                $query->where('is_has_category', request('is_has_category', 0));
            })
            ->when(request()->filled('template_id'), function ($query) {
                $query->where(function ($query){
                    $query->whereHas('templates',function ($query){
                        $query->where('product_template.template_id',request('template_id'));
                    })->orWhereHas('products.templates',function ($query){
                            $query->where('product_template.template_id',request('template_id'));
                        });;
                });


            })
            ->when(request()->filled('has_categories'), function ($query) {
                $query->where(function ($query) {
                    $query->where('is_has_category', 0)->orWhereHas('products');
                });
            });

        return $paginate ? $query->paginate($perPage) : $query->get();
    }

    public function showResource($id, $relations = [])
    {
        return $this->repository->query()
            ->with($relations)
            ->withLastOfferId()
            ->find($id, $relations);
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $model = $this->repository->create($validatedData);
        if (Arr::has($validatedData, 'image_id')) {
            Media::where('id', $validatedData['image_id'])
                ->update([
                    'model_type' => get_class($model),
                    'model_id' => $model->id,
                    'collection_name' => 'categories',
                ]);
        }
        return $model->load($relationsToLoad);
    }

    public function storeProductWithoutCategories($validatedData)
    {
        return $this->handleTransaction(function () use ($validatedData) {
            $product = $this->repository->create($validatedData);
            $product->tags()->sync($validatedData['tags'] ?? []);
            if (!empty($validatedData['dimensions'])) {
                $product->dimensions()->syncWithoutDetaching($validatedData['dimensions']);
            }

            if (!empty($validatedData['custom_dimensions'])) {

                collect($validatedData['custom_dimensions'])->each(function ($dimension) use ($product) {
                    $dimension = $this->dimensionRepository->create($dimension);
                    $product->dimensions()->syncWithoutDetaching($dimension->id);
                });
            }

            $product->prices()->createMany($validatedData['prices'] ?? []);
            if (isset($validatedData['specifications'])) {
                collect($validatedData['specifications'])->map(function ($specification) use ($product) {
                    $productSpecification = $product->specifications()->create([
                        'name' => [
                            'en' => $specification['name_en'],
                            'ar' => $specification['name_ar'],
                        ],
                    ]);

                    if (isset($specification['specification_options'])) {
                        collect($specification['specification_options'])->each(function ($option, $index) use ($productSpecification, $product) {

                            $productOption = $productSpecification->options()->create([
                                'value' => [
                                    'en' => $option['value_en'],
                                    'ar' => $option['value_ar'],
                                ],
                                'price' => $option['price'],
                            ]);

                            if (isset($option['option_image'])) {
                                Media::where('id', $option['option_image'])
                                    ->update([
                                        'model_type' => get_class($productOption),
                                        'model_id' => $productOption->id,
                                        'collection_name' => 'categorySpecificationOptions',
                                    ]);
                            }
                        });
                    }

                });
            }
            Media::where('id', $validatedData['image_id'])
                ->update([
                    'model_type' => get_class($product),
                    'model_id' => $product->id,
                    'collection_name' => 'categories',
                ]);
            if (isset($validatedData['image_model_id'])) {
                Media::where('id', $validatedData['image_model_id'])
                    ->update([
                        'model_type' => get_class($product),
                        'model_id' => $product->id,
                        'collection_name' => 'category_model_image',
                    ]);
            }
            if (isset($validatedData['images_ids'])) {
                collect($validatedData['images_ids'])->each(function ($imageId) use ($product) {
                    Media::where('id', $imageId)
                        ->update([
                            'model_type' => get_class($product),
                            'model_id' => $product->id,
                            'collection_name' => 'category_extra_images',
                        ]);
                });

            }
            return $product;
        });

    }

    public function updateProductWithoutCategories($id, $validatedData)
    {
        return $this->handleTransaction(function () use ($id, $validatedData) {

            $product = $this->repository->update($validatedData, $id);
            $product->tags()->sync($validatedData['tags'] ?? []);
            if (!empty($validatedData['dimensions'])) {
                $product->dimensions()->sync($validatedData['dimensions']);
            }
            if (!empty($validatedData['custom_dimensions'])) {
                collect($validatedData['custom_dimensions'])->each(function ($dimension) use ($product) {
                    $dimension = $this->dimensionRepository->create($dimension);
                    $product->dimensions()->syncWithoutDetaching($dimension->id);
                });
            }
            if (isset($validatedData['base_price'])) {
                $product->prices()->delete();
            }
            if (isset($validatedData['prices'])) {
                $product->update(['base_price' => null]);

                $submittedQuantities = collect($validatedData['prices'])->map(function ($price) use ($product) {
                    $product->prices()->updateOrCreate(
                        ['quantity' => $price['quantity']],
                        ['price' => $price['price']]
                    );
                    return $price['quantity'];
                })->toArray();

                $product->prices()->whereNotIn('quantity', $submittedQuantities)->delete();
            }


            if (isset($validatedData['specifications'])) {

                $submittedSpecIds = collect($validatedData['specifications'])->map(function ($specification) use ($product) {
                    $productSpecification = $product->specifications()->updateOrCreate(
                        [
                            'id' => $specification['id'] ?? null,
                        ],
                        [
                            'name' => [
                                'en' => $specification['name_en'],
                                'ar' => $specification['name_ar'],
                            ],
                        ]
                    );


                    $submittedOptionIds = collect($specification['specification_options'] ?? [])->map(function ($option) use ($productSpecification) {
                        $productOption = $productSpecification->options()->updateOrCreate(
                            ['id' => $option['id'] ?? null],
                            [
                                'value' => [
                                    'en' => $option['value_en'],
                                    'ar' => $option['value_ar'],
                                ],
                                'price' => $option['price'] ?? 0,
                            ]
                        );

                        if (isset($option['option_image'])) {
                            Media::where('id', $option['option_image'])->update([
                                'model_type' => get_class($productOption),
                                'model_id' => $productOption->id,
                                'collection_name' => 'categorySpecificationOptions',
                            ]);
                        }

                        return $productOption->id;
                    })->toArray();


                    $productSpecification->options()->whereNotIn('id', $submittedOptionIds)->each(function ($option) {
                        $option->clearMediaCollection();
                        $option->delete();
                    });

                    return $productSpecification->id;
                })->toArray();


                $product->specifications()->whereNotIn('id', $submittedSpecIds)->each(function ($spec) {
                    $spec->options->each(function ($option) {
                        $option->clearMediaCollection();
                        $option->delete();
                    });
                    $spec->delete();
                });
            } else {

                $product->specifications->each(function ($spec) {
                    $spec->options->each(function ($option) {
                        $option->clearMediaCollection();
                        $option->delete();
                    });
                    $spec->delete();
                });
            }


            if (isset($validatedData['image_id'])) {
                Media::where('id', $validatedData['image_id'])
                    ->update([
                        'model_type' => get_class($product),
                        'model_id' => $product->id,
                        'collection_name' => 'categories',
                    ]);

            }
            if (isset($validatedData['images_ids'])) {
                collect($validatedData['images_ids'])->each(function ($imageId) use ($product) {
                    Media::where('id', $imageId)
                        ->update([
                            'model_type' => get_class($product),
                            'model_id' => $product->id,
                            'collection_name' => 'category_extra_images',
                        ]);
                });
            }
            if (!empty($validatedData['image_model_id'])) {
                $product->getMedia('product_model_image')
                    ->where('id', '!=', $validatedData['image_model_id'])
                    ->each->delete();

                Media::where('id', $validatedData['image_model_id'])
                    ->update([
                        'model_type' => get_class($product),
                        'model_id' => $product->id,
                        'collection_name' => 'category_model_image',
                    ]);
            }

            return $product;
        });


    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        $model = $this->repository->update($validatedData, $id);
        if (Arr::has($validatedData, 'image_id') && !is_null($validatedData['image_id'])) {
            $model->clearMediaCollection('categories');
            Media::where('id', $validatedData['image_id'])
                ->update([
                    'model_type' => get_class($model),
                    'model_id' => $model->id,
                    'collection_name' => 'categories',
                ]);
        }
        return $model->load($relationsToLoad);
    }

    public function getSubCategories()
    {
        return $this->repository->getWithFilters();
    }

    public function getData(): JsonResponse
    {
        $locale = app()->getLocale();
        $categories = $this->repository
            ->query(['id', 'name', 'description', 'created_at', 'is_has_category'])
            ->with(['products', 'children'])
            ->withCount(['children', 'products'])
            ->when(request()->filled('search_value'), function ($query) use ($locale) {
                if (hasMeaningfulSearch(request('search_value'))) {
                    $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?", [
                        '%' . strtolower(request('search_value')) . '%'
                    ]);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })->when(request()->filled('created_at'), function ($query) {
                $query->orderBy('created_at', request('created_at'));
            })
            ->whereNull('parent_id')
            ->latest();

        return DataTables::of($categories)
            ->addColumn('name', function ($category) {
                return $category->getTranslation('name', app()->getLocale());
            })
            ->addColumn('name_en', function ($category) {
                return $category->getTranslation('name', 'en');
            })
            ->addColumn('name_ar', function ($category) {
                return $category->getTranslation('name', 'ar');
            })
            ->addColumn('description_en', function ($category) {
                return $category->getTranslation('description', 'en');
            })
            ->addColumn('description_ar', function ($category) {
                return $category->getTranslation('description', 'ar');
            })
            ->addColumn('products', function ($category) {
                return !empty($category->products)
                    ? $category->products->pluck('name')
                    : [];
            })
            ->addColumn('imageId', function ($category) {
                return $category->getFirstMedia('categories')?->id;
            })
            ->addColumn('added_date', function ($category) {
                return $category->created_at?->format('d/n/Y');
            })
            ->addColumn('show_date', function ($category) {
                return $category->created_at?->format('Y-m-d');
            })
            ->addColumn('sub_categories', function ($category) {
                return $category->is_has_category ? $category->children_count : "-";
            })
            ->addColumn('no_of_products', function ($category) {
                return $category->is_has_category ? $category->products_count : "-";
            })
            ->addColumn('image', function ($admin) {
                return $admin->getFirstMediaUrl('categories') ?: asset("images/default-user.png");
            })
            ->make(true);
    }


    public function getSubCategoryData(): JsonResponse
    {
        $locale = app()->getLocale();
        $categories = $this->repository
            ->query(['id', 'name', 'parent_id', 'created_at'])
            ->with(['parent'])
            ->withCount(['subCategoryProducts'])
            ->whereNotNull('parent_id')
            ->when(request()->filled('search_value'), function ($query) use ($locale) {
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?", [
                    '%' . strtolower(request('search_value')) . '%'
                ]);
            })->when(request()->filled('created_at'), function ($query) {
                $query->orderBy('created_at', request('created_at'));
            })
            ->latest();
        return DataTables::of($categories)
            ->addColumn('name', function ($category) {
                return $category->getTranslation('name', app()->getLocale());
            })
            ->addColumn('name_en', function ($category) {
                return $category->getTranslation('name', 'en');
            })
            ->addColumn('name_ar', function ($category) {
                return $category->getTranslation('name', 'ar');
            })
            ->addColumn('parent_name', function ($category) {
                return $category->parent->getTranslation('name', app()->getLocale());
            })
            ->addColumn('added_date', function ($category) {
                return $category->created_at?->format('d/n/Y');
            })
            ->addColumn('show_date', function ($category) {
                return $category->created_at?->format('Y-m-d');
            })
            ->addColumn('no_of_products', function ($category) {
                return $category->products_count;
            })->make();
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

    public function addToLanding($validatedData, $categoryId)
    {
        if ($this->repository->query()->isLanding()->count() == 7) {
            throw ValidationException::withMessages([
                'category_id' => ['you can\'t add more than 7 items.']
            ]);
        }
        $category = $this->repository->find($categoryId);
        return $this->handleTransaction(function () use ($category, $validatedData) {
            $category = tap($category, function ($category) {
                $category->update(['is_landing' => true]);
            });
            $category->landingProducts()->syncWithoutDetaching(Arr::get($validatedData, 'products') ?? []);
            $category->landingSubCategories()->syncWithoutDetaching(Arr::get($validatedData, 'sub_categories') ?? []);

            return $category;
        });
    }

    public function editCategoryOnLanding($validatedData, $categoryId)
    {
        $category = $this->repository->find($categoryId);
        return $this->handleTransaction(function () use ($category, $validatedData) {
            $category->landingProducts()->sync(Arr::get($validatedData, 'products') ?? []);
            $category->landingSubCategories()->sync(Arr::get($validatedData, 'sub_categories') ?? []);

            return $category;
        });
    }

    public function removeFromLanding($categoryId)
    {
        $category = $this->repository->find($categoryId);
        return $this->handleTransaction(function () use ($category) {
            $category = tap($category, function ($category) {
                $category->update(['is_landing' => false]);
            });
            $category->landingProducts()->detach();

            $category->landingSubCategories()->detach();

            return $category;
        });
    }

}
