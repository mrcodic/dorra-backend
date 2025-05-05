<?php

namespace App\Services;

use App\Filters\SubCategoryFilter;
use App\Models\Product;
use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Yajra\DataTables\Facades\DataTables;

class ProductService extends BaseService
{

    public BaseRepositoryInterface $repository;
    protected array $relations;

    public function __construct(ProductRepositoryInterface $repository)
    {
        $this->relations = ['category', 'tags', 'reviews'];

//        $this->filters = [
//            AllowedFilter::callback('name', function ($query, $value) {
//                $locale = app()->getLocale();
//                $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"')) LIKE ?", ["%{$value}%"]);
//            }),
//        ];

        parent::__construct($repository);
    }


    public function getData()
    {
        $products = QueryBuilder::for(Product::class)
            ->with($this->relations)
            ->withCount(['category', 'tags',])
            ->allowedFilters([
                AllowedFilter::callback('name',function ($query, $value){
                        $locale = app()->getLocale();
                        $query->where("name->{$locale}", 'LIKE', "%{$value}%");
                }),

            ])
            ->latest()
            ->get();
        return DataTables::of($products)
            ->addColumn('name', function (Product $product) {
                return $product->getTranslation('name', app()->getLocale());
            })
            ->addColumn('added_date', function ($product) {
                return $product->created_at?->format('j/n/Y');
            })
            ->addColumn('category', function ($product) {
                return $product->category?->name ?? 'uncategorized';
            })
            ->addColumn('tags', function ($product) {
                return $product->tags?->pluck('name');
            })
            ->addColumn('rating', function ($product) {
                return $product->reviews?->pluck('rating')->avg() ?? 0;
            })
            ->addColumn('no_of_purchas', function ($product) {
                return 0;
            })->make();
    }

    public function getAll(bool $paginate = false, $columns = ['*']): LengthAwarePaginator
    {
        return QueryBuilder::for(Product::class)->select($columns)
            ->with($this->relations)
            ->withCount('reviews')
            ->allowedFilters([
                AllowedFilter::partial('category.id'),
                AllowedFilter::custom('sub_categories', new SubCategoryFilter()),
                AllowedFilter::scope('with_review_rating'),
            ])
            ->paginate();

    }

    public function storeResource($validatedData, $relationsToStore = [])
    {
        $product = $this->repository->create($validatedData);
        $product->load($this->relations);
        $product->tags()->sync($validatedData['tags'] ?? []);
        $product->prices()->createMany($validatedData['prices'] ?? []);

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

        handleMediaUploads($validatedData['image'], $product, 'product_main_image');
        if (isset($validatedData['images'])) {
            handleMediaUploads($validatedData['images'], $product, 'product_extra_images');

        }

        return $product;
    }

    public function updateResource($id, $validatedData)
    {
        $product = $this->repository->update($validatedData, $id);
        $product->load($this->relations);
        $product->tags()->sync($validatedData['tags'] ?? []);
        if (isset($validatedData['prices'])) {
            collect($validatedData['prices'])->each(function ($price) use ($product) {
                $product->prices()->update($price);
            });

        }
        collect($validatedData['specifications'])->map(function ($specification) use ($product) {
            $productSpecification = tap($product->specifications()->first(), function ($spec) use ($specification) {
                $spec->update([
                    'name' => [
                        'en' => $specification['name_en'],
                        'ar' => $specification['name_ar'],
                    ],
                ]);
            });


            collect($specification['specification_options'])->each(function ($option) use ($productSpecification) {
                $productOption = tap($productSpecification->options()->first(),function ($spec) use ($option){
                    $spec->update([
                        'value' => [
                            'en' => $option['value_en'],
                            'ar' => $option['value_ar'],
                        ],
                        'price' => $option['price'],
                    ]);
                });

                if (isset($option['image'])) {
                    if ($option['image'] instanceof UploadedFile) {
                        handleMediaUploads([$option['image']], $productOption);
                    }
                }
            });


        });
        if (isset($validatedData['image'])) {
            handleMediaUploads($validatedData['image'], $product, 'product_main_image', clearExisting: true);
        }
        if (isset($validatedData['images'])) {
            handleMediaUploads($validatedData['images'], $product, 'product_extra_images');

        }

        return $product;
    }

}
