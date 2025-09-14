<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\SubCategory\{StoreSubCategoryRequest, UpdateSubCategoryRequest};
use Illuminate\Http\JsonResponse;
use \Illuminate\Http\Request;

class SubCategoryController extends DashboardController
{

    public function __construct(public CategoryService             $categoryService,
                                public CategoryRepositoryInterface $categoryRepository,)
    {

        parent::__construct($categoryService);
        $this->storeRequestClass = new StoreSubCategoryRequest();
        $this->updateRequestClass = new UpdateSubCategoryRequest();
        $this->indexView = 'subcategories.index';
        $this->assoiciatedData = [
            'index' => [
                'categories' => $this->categoryRepository->query()->whereNull('parent_id')->whereIsHasCategory(1)->get(['id', 'name']),
            ],
        ];
        $this->usePagination = true;
        $this->resourceTable = 'categories';
    }

    public function getData(): JsonResponse
    {
        return $this->categoryService->getSubCategoryData();
    }
}
