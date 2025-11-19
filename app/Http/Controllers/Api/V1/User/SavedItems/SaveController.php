<?php

namespace App\Http\Controllers\Api\V1\User\SavedItems;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\SavedItems\DeleteSaveRequest;
use App\Http\Requests\User\SavedItems\ToggleSaveRequest;
use App\Http\Resources\Design\DesignResource;
use App\Http\Resources\Product\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Facades\Response;

class SaveController extends Controller
{

    public function savedItems(CategoryRepositoryInterface $categoryRepository)
    {
        $user = auth('sanctum')->user();
        $category = $categoryRepository->query()->find(request('category_id'));
        $savedProducts = $user->savedProducts()
            ->with('category','media')
            ->when(request()->filled('category_id'), function ($query) {
                $query->whereRelation('category', 'id', request('category_id'));
            })
            ->orderBy('created_at', request('date','desc'))
            ->get();

        $savedDesigns = $user->savedDesigns()
            ->with('designable','owner','media','productPrice')
            ->when(request()->filled('category_id'), fn($q) => $q->whereIn('designable_id', $category?->is_has_category ?
                $category?->products->pluck('id') :
                [request('category_id')])
                ->where('designable_type', $category?->is_has_category ? Product::class : Category::class))
            ->orderBy('created_at', request('date','desc'))
            ->latest()
            ->get();


        return Response::api(data: [
            'designs' => $savedDesigns->isNotEmpty() ? DesignResource::collection($savedDesigns) : [],
            'products' => $savedProducts->isNotEmpty() ? ProductResource::collection($savedProducts) : [],
        ]);

    }
    public function toggleSave(ToggleSaveRequest $request)
    {
        $relation = "saved".ucfirst($request->savable_type)."s";
        $saved = [];
        $unsaved = [];
        collect($request->savable_ids)->map(function ($id) use ($request, $relation, &$saved, &$unsaved) {
            $request->user()->$relation()->toggle($id);
            $isNowSaved = $request->user()->$relation()->whereKey($id)->exists();
            if ($isNowSaved) {
                $saved[] = $id;
            } else {
                $unsaved[] = $id;
            }
        });
        return Response::api(data: [
            'saved' => $saved,
            'unsaved' => $unsaved,
            'savable_type' => $request->savable_type,
        ]);
    }

    public function destroyBulk(DeleteSaveRequest $request)
    {
        $relation = "saved".ucfirst($request->savable_type)."s";
        $request->user()->$relation()->detach($request->savable_ids);

        return Response::api(data: [
            'status' => 'unsaved',
            'saveable_type' => $request->savable_type,
            'saveable_ids' => $request->savable_ids
        ]);
    }
}
