<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\OfferService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Offer\{
    StoreOfferRequest,
    UpdateOfferRequest
};

class OfferController extends DashboardController
{
    public function __construct(
        public OfferService $offerService,
        public CategoryRepositoryInterface $categoryRepository,
        public ProductRepositoryInterface $productRepository,
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
                'product_with_categories' => $this->categoryRepository
                    ->query()
                    ->where('is_has_category', 1)
                    ->where('is_tableau', 0)
                    ->has('products')
                    ->where(function ($query) {
                        $query
                            ->whereDoesntHave('offers')
                            ->orWhereHas('offers', function ($query) {
                                $query->where(
                                    'offers.end_at',
                                    '<=',
                                    now()
                                );
                            });
                    })
                    ->get([
                        'id',
                        'name',
                    ]),
                'product_without_categories' => $this->categoryRepository
                    ->query()
                    ->where('is_has_category', 0)
                    ->where('is_tableau', 0)
                    ->where(function ($query) {
                        $query
                            ->whereDoesntHave('offers')
                            ->orWhereHas('offers', function ($query) {
                                $query->where(
                                    'offers.end_at',
                                    '<=',
                                    now()
                                );
                            });
                    })
                    ->get([
                        'id',
                        'name',
                    ]),


                /*
                 * Edit:
                 * return everything because the current offer's
                 * selected items must still appear while editing.
                 */
                'editProductWithCategories' => $this->categoryRepository
                    ->query()
                    ->where('is_has_category', 1)
                    ->where('is_tableau', 0)
                    ->has('products')
                    ->get([
                        'id',
                        'name',
                    ]),

                'editProductWithoutCategories' => $this->categoryRepository
                    ->query()
                    ->where('is_has_category', 0)
                    ->where('is_tableau', 0)
                    ->get([
                        'id',
                        'name',
                    ]),
            ]
        ];
    }


    public function getData(): JsonResponse
    {
        return $this->offerService->getData();
    }
}
