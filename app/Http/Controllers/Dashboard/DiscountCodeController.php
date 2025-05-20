<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\DiscountCodeService;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\DiscountCode\{StoreDiscountCodeRequest, UpdateDiscountCodeRequest};



class DiscountCodeController extends DashboardController
{
    public function __construct(public DiscountCodeService         $discountCodeService,
                                public CategoryRepositoryInterface $categoryRepository
                               ,public ProductRepositoryInterface    $productRepository)
    {
        parent::__construct($discountCodeService);
        $this->storeRequestClass = new StoreDiscountCodeRequest();
        $this->updateRequestClass = new UpdateDiscountCodeRequest();
        $this->indexView = 'discount-codes.index';
        $this->createView = 'discount-codes.create';
        $this->editView = 'discount-codes.edit';
        $this->usePagination = true;
        $this->resourceTable = 'discount_codes';
        $this->assoiciatedData = [
            'index' => [
                'categories' => $this->categoryRepository->query()->whereNull('parent_id')->get(['id', 'name']),
                'products' => $this->productRepository->all(columns: ['id', 'name']),
            ],
        ];
    }

    public function getData()
    {
        return $this->discountCodeService->getData();
    }

    public function generateAndExport(StoreDiscountCodeRequest $request)
    {

        return $this->discountCodeService->generateAndExport($request->validated());
    }


}
