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

        parent::__construct($repository);
    }


    public function getData()
    {
        $products = $this->repository
            ->query()
            ->with($this->relations)
            ->withCount(['category', 'tags',])
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

    public function getAll($relations = [], bool $paginate = false, $columns = ['*']): LengthAwarePaginator
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

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
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

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        $product = $this->repository->update($validatedData, $id);
        $product->load($this->relations);
        $product->tags()->sync($validatedData['tags'] ?? []);
        if (isset($validatedData['base_price'])){
            $product->prices()->delete();
        }
        if (isset($validatedData['prices'])) {
            $product->update(['base_price'=>null]);
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
        collect($validatedData['specifications'])->each(function ($specification) use ($product) {
            $productSpecification = $product->specifications()->updateOrCreate(
                [
                    'name->en' => $specification['name_en'],
                    'name->ar' => $specification['name_ar'],
                ],
                []
            );

            collect($specification['specification_options'])->each(function ($option) use ($productSpecification) {
                $productOption = $productSpecification->options()->updateOrCreate(
                    [
                        'value->en' => $option['value_en'],
                        'value->ar' => $option['value_ar'],
                    ],
                    [
                        'price' => $option['price'] ?? 0,
                    ]
                );

                if (!empty($option['image']) && $option['image'] instanceof UploadedFile) {
                    handleMediaUploads([$option['image']], $productOption);
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
