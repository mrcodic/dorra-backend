<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Resources\CategoryResource;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\DimensionRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Http\Requests\Category\{StoreCategoryRequest,
    StoreProductWithoutCategoryRequest,
    UpdateCategoryRequest,
    UpdateProductWithoutCategoryRequest};


class CategoryController extends DashboardController
{
    public function __construct(
        public CategoryService              $categoryService,
        public TagRepositoryInterface       $tagRepository,
        public DimensionRepositoryInterface $dimensionRepository,
        public CategoryRepositoryInterface $categoryRepository,
    )
    {
        parent::__construct($categoryService);
        $this->storeRequestClass = new StoreCategoryRequest();
        $this->updateRequestClass = new UpdateCategoryRequest();
        $this->indexView = 'categories.index';
        $this->createView = 'categories.create';
        $this->showView = 'categories.show';
        $this->editView = 'categories.edit';
        $this->usePagination = true;
        $this->resourceClass = CategoryResource::class;
        $this->resourceTable = 'categories';
        $this->assoiciatedData = [
            'shared' => [
                'tags' => $this->tagRepository->all(columns: ['id', 'name']),
                'dimensions' => $this->dimensionRepository->query()->whereIsCustom(false)->get(['id', 'name']),
            ],

        ];
    }

    public function getData(): JsonResponse
    {
        return $this->categoryService->getData();
    }

    public function search(Request $request)
    {
        $categories = $this->categoryService->search($request);
        return $this->resourceClass::collection($categories);
    }

    public function addToLanding(Request $request)
    {
        $validatedData = $request->validate([
            'category_id' => ['required', 'exists:categories,id',function ($attribute, $value, $fail) {
                $category =$this->categoryRepository->find($value);
                if (!$category) {return;}
                if ($category->is_landing) {
                    $fail("this product is already  a landing product");
                }
            }],
            'sub_categories' => 'sometimes', 'array',
            'sub_categories.*' => ['sometimes', 'exists:categories,id'],
            'products' => ['sometimes', 'array'],
            'products.*' => ['sometimes', 'exists:products,id'],
        ], [
            'category_id.required' => 'Please select a product.',
            'category_id.exists' => 'Selected product does not exist.',
        ]);
        $category = $this->categoryService->addToLanding($validatedData, $request->get('category_id'));
        return $this->resourceClass::make($category);

    }

    public function removeFromLanding(Request $request)
    {
        $request->validate(['category_id' => 'required', 'exists:categories,id']);
        $category = $this->categoryService->removeFromLanding($request->get('category_id'));
        return $this->resourceClass::make($category);

    }

    public  function storeProductWithoutCategories(StoreProductWithoutCategoryRequest $request)
    {
        $this->categoryService->storeProductWithoutCategories($request->validated());
    }

    public  function updateProductWithoutCategories($id,UpdateProductWithoutCategoryRequest $request)
    {
        $this->categoryService->updateProductWithoutCategories($id,$request->validated());
        return Response::api();
    }

}
