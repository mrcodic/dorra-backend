<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Requests\Offer\{StoreOfferRequest, UpdateOfferRequest};
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\OfferService;
use Illuminate\Http\JsonResponse;

class OfferController extends DashboardController
{
    public function __construct(
        public OfferService $offerService,
        public CategoryRepositoryInterface $categoryRepository,
        public ProductRepositoryInterface $productRepository,
    ) {
        parent::__construct($offerService);

        $this->storeRequestClass = new StoreOfferRequest();
        $this->updateRequestClass = new UpdateOfferRequest();
        $this->indexView = 'offers.index';
        $this->usePagination = true;
        $this->resourceTable = 'offers';
        $productWithCategories = $this->categoryRepository
            ->query()
            ->where('is_has_category', 1)
            ->where('is_tableau', 0)
            ->has('products')
            ->whereDoesntHave('offers', function ($query) {
                $query->where('offers.end_at', '>', now());
            })
            ->get(['id', 'name']);

        $productWithoutCategories = $this->categoryRepository
            ->query()
            ->where('is_has_category', 0)
            ->where('is_tableau', 0)
            ->whereDoesntHave('offers', function ($query) {
                $query->where('offers.end_at', '>', now());
            })
            ->get(['id', 'name']);

        $editCategories = $this->categoryRepository
            ->query()
            ->whereNull('parent_id')
            ->whereIsHasCategory(0)
            ->get(['id', 'name']);

        $editProducts = $this->productRepository
            ->query()
            ->get(['id', 'name']);

        $this->assoiciatedData = [
            'index' => [
                'product_with_categories' => $productWithCategories,
                'product_without_categories' => $productWithoutCategories,

                'categories' => $productWithCategories,
                'products' => $productWithoutCategories,
                'editCategories' => $editCategories,
                'editProducts' => $editProducts,
            ],
        ];
    }

    public function getData(): JsonResponse
    {
        return $this->offerService->getData();
    }
}
