<?php

namespace App\Services;

use App\Filters\SubCategoryFilter;
use App\Models\Product;
use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\DimensionRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\ProductSpecificationRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Yajra\DataTables\Facades\DataTables;

class ProductService extends BaseService
{

    public BaseRepositoryInterface $repository;
    public $relations;


    public function __construct(ProductRepositoryInterface                     $repository,
                                public ProductSpecificationRepositoryInterface $specificationRepository,
                                public DimensionRepositoryInterface            $dimensionRepository,
    )
    {
        $this->relations = ['category', 'tags', 'reviews', 'saves' => function ($query) {
            $query->where('user_id', auth('sanctum')->id());
        },];

        parent::__construct($repository);
    }


    public function getData()
    {
        $products = $this->repository
            ->query()
            ->with($this->relations)
            ->withCount(['category', 'tags','confirmedOrders'])
            ->when(request()->filled('search_value'), function ($query) {
                $locale = app()->getLocale();
                $search = request('search_value');
                $query->where("name->{$locale}", 'LIKE', "%{$search}%");
            })
            ->when(request()->filled('category_id'), function ($query) {
                $query->whereCategoryId(request()->get('category_id'));
            })
            ->when(request()->filled('tag_id'), function ($query) {
                $query->whereHas('tags', function ($query) {
                    $query->whereKey(request('tag_id'));
                });
            })
            ->latest();

        return DataTables::of($products)
            ->addColumn('name', function (Product $product) {
                return $product->getTranslation('name', app()->getLocale());
            })
            ->addColumn('added_date', function ($product) {
                return $product->created_at?->format('j/n/Y');
            })
//            ->addColumn('category', function ($product) {
//                return $product->category?->name ?? 'uncategorized';
//            })
            ->addColumn('tags', function ($product) {
                return $product->tags?->pluck('name');
            })
            ->addColumn('rating', function ($product) {
                return $product->reviews?->pluck('rating')->avg() ?? 0;
            })
            ->addColumn('image', function ($product) {
                return $product->getMainImageUrl();
            })
            ->addColumn('no_of_purchas', function ($product) {
                return $product->confirmed_orders_count;
            })->make();
    }

    public function getAll($relations = [], bool $paginate = false, $columns = ['*'], $perPage = 9)
    {
        $locale = App::getLocale();

        $query = QueryBuilder::for(Product::class)
            ->select($columns)
            ->with($relations)
            ->withCount('reviews')
            ->when(request()->filled('search_value'), function ($query) use ($locale) {
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?", [
                    '%' . strtolower(request()->search) . '%'
                ]);
            })
            ->when(request()->filled('templates'), function ($q) {
                $templates = request('templates');
                $q->whereHas('templates', function ($q) use ($templates) {
                    $q->whereIn('templates.id', is_array($templates) ? $templates : [$templates]);
                });
            })
            ->allowedFilters([
                AllowedFilter::partial('category.id'),
                AllowedFilter::custom('sub_categories', new SubCategoryFilter()),
                AllowedFilter::scope('with_review_rating'),
            ])
            ->latest();

        if (request()->has('limit') && is_numeric(request('limit'))) {
            return $query->take((int)request('limit'))->get();
        }

        return $paginate ? $query->paginate($perPage) : $query->get();
    }


    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        return $this->handleTransaction(function () use ($validatedData, $relationsToStore, $relationsToLoad) {
            $product = $this->repository->create($validatedData);
            $product->load($this->relations);
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


                    collect($specification['specification_options'])->each(function ($option, $index) use ($productSpecification) {

                        $productOption = $productSpecification->options()->create([
                            'value' => [
                                'en' => $option['value_en'],
                                'ar' => $option['value_ar'],
                            ],
                            'price' => $option['price'],
                        ]);

                        if (isset($option['image'])) {
                            if ($option['image'] instanceof UploadedFile) {
                                handleMediaUploads([$option['image']], $productOption);
                            }
                        }
                    });


                });
            }
            handleMediaUploads($validatedData['image'], $product, 'product_main_image');
            if (isset($validatedData['images'])) {
                handleMediaUploads($validatedData['images'], $product, 'product_extra_images');

            }
            return $product;
        });


    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        $product = $this->repository->update($validatedData, $id);
        $product->load($this->relations);
        $product->tags()->sync($validatedData['tags'] ?? []);
        if (!empty($validatedData['dimensions'])) {
            $product->dimensions()->sync($validatedData['dimensions']);
        }
        if (!empty($validatedData['custom_dimensions'])) {
            collect($validatedData['custom_dimensions'])->each(function ($dimension) use ($product) {
                $dimension = $this->dimensionRepository->create($dimension);
                $product->dimensions()->sync($dimension->id);
            });
        }
        if (request()->has('deleted_old_images')) {
            collect(request()->deleted_old_images)->each(function ($id) {
                Media::find($id)?->delete();
            });
        }

        if (isset($validatedData['base_price'])) {
            $product->prices()->delete();
        }
        if (isset($validatedData['prices'])) {
            $product->update(['base_price' => null]);
            collect($validatedData['prices'])->each(function ($price) use ($product) {
                $product->prices()->updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'quantity' => $price['quantity'],
                    ],
                    [
                        'price' => $price['price'],
                    ]
                );
            });
        }
        if (isset($validatedData['specifications'])) {
            collect($validatedData['specifications'])->each(function ($specification) use ($product) {
                $productSpecification = $product->specifications()->updateOrCreate(
                    [
                        'name->en' => $specification['name_en'],
                        'name->ar' => $specification['name_ar'],
                    ],
                    [
                        'name' => [
                            'en' => $specification['name_en'],
                            'ar' => $specification['name_ar'],
                        ],
                    ]
                );

                if (isset($specification['specification_options'])) {
                    collect($specification['specification_options'])->each(function ($option) use ($productSpecification) {
                        $productOption = $productSpecification->options()->updateOrCreate(
                            [
                                'value->en' => $option['value_en'],
                                'value->ar' => $option['value_ar'],
                            ],
                            [
                                'value' => [
                                    'en' => $option['value_en'],
                                    'ar' => $option['value_ar'],
                                ],
                                'price' => $option['price'] ?? 0,
                            ]
                        );

                        if (!empty($option['image']) && $option['image'] instanceof UploadedFile) {
                            handleMediaUploads([$option['image']], $productOption, clearExisting: true);
                        }
                    });
                }

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

        if (isset($validatedData['image'])) {
            handleMediaUploads($validatedData['image'], $product, 'product_main_image', clearExisting: true);
        }
        if (isset($validatedData['images'])) {
            handleMediaUploads($validatedData['images'], $product, 'product_extra_images');

        }
        return $product;
    }

    public function search($request)
    {
        $tagIds = is_array($request->tag_ids) ? $request->tag_ids : Arr::wrap($request->tag_ids);
        $locale = App::getLocale();

        return $this->repository->query()
            ->when($request->filled('search'), function ($query) use ($request, $locale) {
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?", [
                    '%' . strtolower($request->search) . '%'
                ]);
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->when(!empty($tagIds), function ($query) use ($tagIds) {
                $query->whereHas('tags', function ($q) use ($tagIds) {
                    $q->whereIn('tags.id', $tagIds);
                }, '=', count($tagIds));
            })
            ->get();
    }


    public function productSpecifications($productId)
    {
        return $this->specificationRepository->query()->whereProductId($productId)->get();
    }

    public function storeProductSpecification($validatedData)
    {
        $product = $this->repository->find($validatedData['product_id']);
        return $this->handleTransaction(function () use ($product, $validatedData) {
            $productSpecification = $product->specifications()->create([
                'name' => [
                    'en' => $validatedData['specifications'][0]['name_en'],
                    'ar' => $validatedData['specifications'][0]['name_ar'],
                ]
            ]);
            collect($validatedData['specifications'][0]['specification_options'])->each(function ($option, $index) use ($productSpecification) {

                $productOption = $productSpecification->options()->create([
                    'value' => [
                        'en' => $option['value_en'],
                        'ar' => $option['value_ar'],
                    ],
                    'price' => $option['price'],
                ]);

                if (isset($option['image'])) {
                    if ($option['image'] instanceof UploadedFile) {
                        handleMediaUploads([$option['image']], $productOption);
                    }
                }
            });
            return $productSpecification;
        });


    }

    public function getQuantities($productId)
    {
        $product = $this->repository->find($productId);
        return $product->prices->pluck('quantity', 'id')->toArray();
    }

}
