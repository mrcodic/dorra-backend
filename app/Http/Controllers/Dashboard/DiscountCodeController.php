<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Services\DiscountCodeService;
use App\Http\Requests\Category\{StoreCategoryRequest, UpdateCategoryRequest};
use Illuminate\Http\JsonResponse;


class DiscountCodeController extends DashboardController
{
    public function __construct(public DiscountCodeService $discountCodeService)
    {
        parent::__construct($discountCodeService);
        $this->storeRequestClass = new StoreCategoryRequest();
        $this->updateRequestClass = new UpdateCategoryRequest();
        $this->indexView = 'discount-codes.index';
        $this->createView = 'discount-codes.create';
        $this->editView = 'discount-codes.edit';
        $this->usePagination = true;
        $this->resourceTable = 'discount_codes';
    }

    public function getData(): JsonResponse
    {
        return $this->discountCodeService->getData();
    }


}
