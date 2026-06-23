<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Resources\TableauSceneResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpecification;
use App\Services\TableauSceneService;
use Illuminate\Http\Request;
use App\Http\Requests\TableauScene\{StoreTableauSceneRequest};



class TableauSceneController extends DashboardController
{
    public function __construct(public TableauSceneService $sceneService)
    {
        parent::__construct($sceneService);
        $this->storeRequestClass = new StoreTableauSceneRequest();
        $this->resourceTable = 'tableau_scenes';
        $this->resourceClass = TableauSceneResource::class;

    }
    public function tableauSize(Request $request)
    {
        $data = $request->validate([
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer'],
        ]);

        $productIds = collect($data['product_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        $categoryIds = collect($data['category_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        $query = ProductSpecification::query()
            ->where('fixed_key', 'tableau_size')
            ->where(function ($q) use ($productIds, $categoryIds) {
                if ($productIds->isNotEmpty()) {
                    $q->orWhere(function ($qq) use ($productIds) {
                        $qq->where('specifiable_type', Product::class)
                            ->whereIn('specifiable_id', $productIds);
                    });
                }

                if ($categoryIds->isNotEmpty()) {
                    $q->orWhere(function ($qq) use ($categoryIds) {
                        $qq->where('specifiable_type', Category::class)
                            ->whereIn('specifiable_id', $categoryIds);
                    });
                }
            });

        $specifications = $query
            ->orderByRaw("CASE WHEN specifiable_type = ? THEN 0 ELSE 1 END", [Product::class])
            ->get()
            ->map(function ($spec) {
                return [
                    'id' => $spec->id,
                    'name' => $spec->name,
                    'fixed_key' => $spec->fixed_key,
                    'type' => $spec->type,
                    'specifiable_id' => $spec->specifiable_id,
                    'specifiable_type' => $spec->specifiable_type,
                ];
            })
            ->values();

        return response()->api(data:[
            $specifications
        ]);
    }
}
