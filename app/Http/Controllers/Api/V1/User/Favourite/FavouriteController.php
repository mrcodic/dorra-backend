<?php

namespace App\Http\Controllers\Api\V1\User\Favourite;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Favourite\ToggleFavouriteRequest;
use App\Http\Resources\Favourite\FavouriteItemResource;
use App\Services\FavouriteService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Response;

class FavouriteController extends Controller
{
    public function __construct(public FavouriteService $favouriteService)
    {
    }

    public function index()
    {
        $paginate = request()->boolean('paginate', false);
        $perPage = max(1, min(request()->integer('per_page', 15), 100));

        $items = $this->favouriteService->items(
            paginate: $paginate,
            perPage: $perPage,
        );

        if ($items instanceof LengthAwarePaginator) {
            $items->setCollection(
                $items->getCollection()
                    ->map(fn ($item) => FavouriteItemResource::make($item)->resolve(request()))
            );

            return Response::api(data: $items);
        }

        return Response::api(data: [
            'items' => FavouriteItemResource::collection($items)->resolve(request()),
            'count' => $items->count(),
        ]);
    }

    public function toggle(ToggleFavouriteRequest $request)
    {
        $result = $this->favouriteService->toggle($request->validated());

        return Response::api(data: [
            'cookie_value' => $result['cookie_value'],
            'is_favourite' => $result['is_favourite'],
        ]);
    }
}
