<?php

namespace App\Http\Controllers\Api\V1\User\Favourite;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Favourite\ToggleFavouriteRequest;
use App\Http\Resources\Favourite\FavouriteItemResource;
use App\Services\FavouriteService;
use Illuminate\Support\Facades\Response;

class FavouriteController extends Controller
{
    public function __construct(public FavouriteService $favouriteService)
    {
    }

    public function index()
    {
        $items = $this->favouriteService->items();

        return Response::api(data: [
            'items' => FavouriteItemResource::collection($items)->resolve(request()),
            'count' => $items->count(),
        ]);
    }

    public function toggle(ToggleFavouriteRequest $request)
    {
        $result = $this->favouriteService->toggle($request->validated());

        return Response::api(data: [
            'is_favourite' => $result['is_favourite'],
            'favouritable_type' => $result['favouritable_type'],
            'favouritable_id' => $result['favouritable_id'],
            'contextable_type' => $result['contextable_type'],
            'contextable_id' => $result['contextable_id'],
            'item' => $result['item']
                ? FavouriteItemResource::make($result['item'])->resolve(request())
                : null,
        ]);
    }
}
