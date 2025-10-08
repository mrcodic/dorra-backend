<?php

namespace App\Services;

use App\Filters\SubCategoryFilter;
use App\Models\CartItem;
use App\Models\Product;
use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\DimensionRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\ProductSpecificationRepositoryInterface;
use Illuminate\Http\Request;
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
                                public CategoryRepositoryInterface             $categoryRepository,
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
            ->withCount(['category', 'tags', 'confirmedOrders'])
            ->when(request()->filled('search_value'), function ($query) {
                if (hasMeaningfulSearch(request('search_value'))) {
                    $locale = app()->getLocale();
                    $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?", [
                        '%' . strtolower(request('search_value')) . '%'
                    ]);
                } else {
                    $query->whereRaw('1 = 0');
                }
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
                return $product->tags?->pluck('name') ?? "-";
            })
            ->addColumn('rating', function ($product) {
                return $product->rating ?? 0;
            })
            ->addColumn('image', function ($product) {
                return $product->getMainImageUrl() ?: asset('images/default-product.png');
            })
            ->addColumn('no_of_purchas', function ($product) {
                return $product->confirmed_orders_count;
            })->make();
    }

    public function getAll($relations = [], bool $paginate = false, $columns = ['*'], $perPage = 9, $counts = [])
    {
        $locale = App::getLocale();

        $query = QueryBuilder::for(Product::class)
            ->select($columns)
            ->withLastOfferId()
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
                AllowedFilter::scope('with_review_rating')->default([]),
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
                                        'collection_name' => 'productSpecificationOptions',
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
                    'collection_name' => 'product_main_image',
                ]);
            if (isset($validatedData['image_model_id'])) {
                Media::where('id', $validatedData['image_model_id'])
                    ->update([
                        'model_type' => get_class($product),
                        'model_id' => $product->id,
                        'collection_name' => 'product_model_image',
                    ]);
            }
            if (isset($validatedData['images_ids'])) {
                collect($validatedData['images_ids'])->each(function ($imageId) use ($product) {
                    Media::where('id', $imageId)
                        ->update([
                            'model_type' => get_class($product),
                            'model_id' => $product->id,
                            'collection_name' => 'product_extra_images',
                        ]);
                });

            }
            return $product;
        });


    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        return $this->handleTransaction(function () use ($id, $validatedData) {

            $product = $this->repository->update($validatedData, $id);
            $product->load($this->relations);
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

                if (($validatedData['has_custom_prices'] ?? false) && $product->has_custom_prices !== 1) {
                    CartItem::where('cartable_id', $product->id)->get()->each(function ($item) use ($validatedData) {
                        $item->update([
                            'quantity' => $validatedData['prices'][0]['quantity'],
                            'product_price' => $validatedData['prices'][0]['price'],
                            'sub_total' => ($validatedData['prices'][0]['price'] * $validatedData['prices'][0]['quantity'])
                                + $item->specs_price
                                - $item->cart->discount_amount,
                        ]);
                    });
                }

                $submittedQuantities = collect($validatedData['prices'])->map(function ($price) use ($product) {
                    $product->prices()->updateOrCreate(
                        ['quantity' => $price['quantity']],
                        ['price' => $price['price']]
                    );
                    return $price['quantity'];
                })->toArray();

                $product->prices()->whereNotIn('quantity', $submittedQuantities)->delete();
            }


                if (!empty($validatedData['specifications'])) {
                    dd($validatedData['specifications']);
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


//                    $submittedOptionIds = collect($specification['specification_options'] ?? [])->map(function ($option) use ($productSpecification) {
//                        $productOption = $productSpecification->options()->updateOrCreate(
//                            ['id' => $option['id'] ?? null],
//                            [
//                                'value' => [
//                                    'en' => $option['value_en'],
//                                    'ar' => $option['value_ar'],
//                                ],
//                                'price' => $option['price'] ?? 0,
//                            ]
//                        );
//
//                        if (isset($option['option_image'])) {
//                            Media::where('id', $option['option_image'])->update([
//                                'model_type' => get_class($productOption),
//                                'model_id' => $productOption->id,
//                                'collection_name' => 'productSpecificationOptions',
//                            ]);
//                        }
//
//                        return $productOption->id;
//                    })->toArray();
//
//
//                    $productSpecification->options()->whereNotIn('id', $submittedOptionIds)->each(function ($option) {
//                        $option->clearMediaCollection();
//                        $option->delete();
//                    });

                    return $productSpecification->id;
                })->toArray();


//                $product->specifications()->whereNotIn('id', $submittedSpecIds)->each(function ($spec) {
//                    $spec->options->each(function ($option) {
//                        $option->clearMediaCollection();
//                        $option->delete();
//                    });
//                    $spec->delete();
//                });
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
                        'collection_name' => 'product_main_image',
                    ]);

            }
            if (isset($validatedData['images_ids'])) {
                collect($validatedData['images_ids'])->each(function ($imageId) use ($product) {
                    Media::where('id', $imageId)
                        ->update([
                            'model_type' => get_class($product),
                            'model_id' => $product->id,
                            'collection_name' => 'product_extra_images',
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
                        'collection_name' => 'product_model_image',
                    ]);
            }

            return $product;
        });
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

    public function getQuantities($productId, Request $request)
    {
        $product = $request->type == 'product' ? $this->repository->find($productId) : $this->categoryRepository->find($productId);

        return $product->prices
            ->sortBy('quantity')
            ->map(function ($price) {
                return [
                    'id'       => $price->id,
                    'quantity' => $price->quantity,
                ];
            })
            ->values()
            ->toArray();
    }

    public function getProductsByCategories($categoryIds)
    {
        return $this->repository->query()->whereIn('category_id', $categoryIds)->get();
    }

}
