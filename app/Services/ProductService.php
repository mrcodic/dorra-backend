<?php

namespace App\Services;

use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Yajra\DataTables\Facades\DataTables;

class ProductService extends BaseService
{

    public BaseRepositoryInterface $repository;
    public function __construct(ProductRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getData()
    {
        $products = $this->repository->query(['id', 'name' , 'created_at'])->with(['category', 'tags',]);
        return DataTables::of($products)
            ->addColumn('added_date',function ($product){
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

}
