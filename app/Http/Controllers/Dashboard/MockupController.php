<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;


use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\MockupService;
use App\Http\Requests\Mockup\{
    StoreMockupRequest,
    UpdateMockupRequest,

};


class MockupController extends DashboardController
{
    public function __construct(
        public MockupService $mockupService,
        public ProductRepositoryInterface              $productRepository,


    )
    {
        parent::__construct($mockupService);
        $this->storeRequestClass = new StoreMockupRequest();
        $this->updateRequestClass = new UpdateMockupRequest();
        $this->indexView = 'mockups.index';
        $this->usePagination = true;
        $this->resourceTable = 'mockups';
        $this->assoiciatedData = [
            'index'=>[
                'products' => $this->productRepository->all(),
            ],
        ];
        $this->methodRelations = [
            'index' => ['product'],
        ];
    }
}
