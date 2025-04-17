<?php

namespace App\Services;

use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Yajra\DataTables\Facades\DataTables;

class ProductService extends BaseService
{

    public BaseRepositoryInterface $repository;
    protected array $relations;
    public function __construct(ProductRepositoryInterface $repository)
    {
        $this->relations = ['category'];
        parent::__construct($repository);
    }

    public function getData()
    {
        $products = $this->repository->query(['id', 'name', 'created_at'])->with(['category', 'tags',]);
        return DataTables::of($products)
            ->addColumn('added_date', function ($product) {
                return $product->created_at?->format('j/n/Y');
            })
            ->addColumn('category', function ($product) {
                return $product->category?->name;
            })
            ->addColumn('tags', function ($product) {
                return $product->tags?->pluck('name');
            })
            ->addColumn('rating', function ($product) {
                return $product->reviews?->pluck('rating')->avg();
            })
            ->addColumn('no_of_purchas', function ($product) {
                return 5;
            })->make();
    }

    public function storeResource($validatedData, $relationsToStore = [])
    {
        $product = $this->repository->create($validatedData);
        $product->load($this->relations);
        $product->tags()->sync($validatedData['tags'] ?? []);
        $product->prices()->createMany($validatedData['prices'] ?? []);

        collect($validatedData['specifications'])->map(function ($specification) use ($product) {
            $productSpecification = $product->specifications()->create(['name' => $specification['name']]);
            $productOptions = $productSpecification->options()->createMany($specification['specification_options'] ?? []);
            collect($specification['specification_options'])->each(function ($option, $index) use ($productOptions) {
                if (isset($option['image'])) {
                    $productOption = $productOptions[$index];
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

}
