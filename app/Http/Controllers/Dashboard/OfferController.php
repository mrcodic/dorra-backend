<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\OfferService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Offer\{StoreOfferRequest, UpdateOfferRequest};


class OfferController extends DashboardController
{
    public function __construct(public OfferService                $offerService,
                                public CategoryRepositoryInterface $categoryRepository,
                                public ProductRepositoryInterface  $productRepository,
    )
    {
        parent::__construct($offerService);
        $this->storeRequestClass = new StoreOfferRequest();
        $this->updateRequestClass = new UpdateOfferRequest();
        $this->indexView = 'offers.index';
        $this->usePagination = true;
        $this->resourceTable = 'offers';
        $this->assoiciatedData = [
            'index' => [
                'categories' => $this->categoryRepository->query()
                    ->whereNull('parent_id')
                    ->whereIsHasCategory(0)
                    ->where(function ($query) {
                        $query->whereDoesntHave('offers')
                            ->orWhereHas('offers',function ($query) {
                                $query->where('offers.end_at', '<=', now());
                            });
                    })
                    ->get(['id', 'name']),
                'editCategories' => $this->categoryRepository->query()
                    ->whereNull('parent_id')
                    ->whereIsHasCategory(0)
                    ->where(function ($query) {
                        $query->whereHas('offers')
                            ->orWhereHas('offers',function ($query) {
                                $query->where('offers.end_at', '>=', now());
                            });
                    })
                    ->get(['id', 'name']),
                'products' => $this->productRepository->query()
                    ->where(function ($query) {
                        $query->whereDoesntHave('offers')
                            ->orWhereHas('offers',function ($query) {
                                $query->where('offers.end_at', '<=', now());
                            });
                    })
                    ->get(['id', 'name']),
                'editProducts' => $this->productRepository->query()
                    ->where(function ($query) {
                        $query->whereHas('offers')
                            ->orWhereHas('offers',function ($query) {
                                $query->where('offers.end_at', '>=', now());
                            });
                    })
                    ->get(['id', 'name']),
            ]
        ];
    }

    public function getData(): JsonResponse
    {
        return $this->offerService->getData();
    }


}
