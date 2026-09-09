<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Requests\Bundle\{StoreBundleRequest, UpdateBundleRequest};
use App\Models\Bundle;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Services\BundleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class BundleController extends DashboardController
{
    public function __construct(
        public BundleService $bundleService,
        public CategoryRepositoryInterface $categoryRepository,
    ) {
        parent::__construct($bundleService);

        $this->storeRequestClass = new StoreBundleRequest();
        $this->updateRequestClass = new UpdateBundleRequest();

        $this->indexView = 'bundles.index';
        $this->usePagination = true;
        $this->resourceTable = 'bundles';

        /*
         * Friendly admin labels:
         * - Products With Categories
         * - Products Without Categories
         *
         * Actual data:
         * - with_category    => Category(is_has_category=1) -> Product
         * - without_category => Category(is_has_category=0) itself is the sellable item
         *
         * No sub-category flow is used here.
         */
        $productWithCategories = $this->categoryRepository
            ->query()
            ->with(['products:id,name,category_id'])
            ->where('is_has_category', 1)
            ->where('is_tableau', 0)
            ->has('products')
            ->get(['id', 'name']);

        $productWithoutCategories = $this->categoryRepository
            ->query()
            ->where('is_has_category', 0)
            ->where('is_tableau', 0)
            ->get(['id', 'name']);

        $displayBundleOnVisitBundleId = Bundle::query()
            ->where('display_bundle_on_visit', true)
            ->value('id');

        $this->assoiciatedData = [
            'index' => [
                'product_with_categories' => $productWithCategories,
                'product_without_categories' => $productWithoutCategories,
                'display_bundle_on_visit_bundle_id' => $displayBundleOnVisitBundleId,
            ],
        ];

        $this->methodRelations = [
            'index' => [],
            'show' => ['trigger.itemable', 'rewards.itemable'],
            'edit' => ['trigger.itemable', 'rewards.itemable'],
            'update' => ['trigger.itemable', 'rewards.itemable'],
            'store' => ['trigger.itemable', 'rewards.itemable'],
        ];
    }

    public function getData(): JsonResponse
    {
        return $this->bundleService->getData();
    }

    public function itemMeta(Request $request)
    {
        $validated = $request->validate([
            'scope' => ['required', 'in:with_category,without_category'],
            'item_id' => ['required', 'integer'],
            'parent_category_id' => ['nullable', 'integer'],
        ]);

        return Response::api(
            data: $this->bundleService->itemMeta(
                $validated['scope'],
                (int) $validated['item_id'],
                isset($validated['parent_category_id'])
                    ? (int) $validated['parent_category_id']
                    : null
            )
        );
    }
}
