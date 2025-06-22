<?php

namespace App\Services;


use App\Models\Cart;
use App\Repositories\Interfaces\CartRepositoryInterface;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class CartService extends BaseService
{
    public function __construct(CartRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $model = $this->repository->query()->firstOrCreate([
            'user_id' => Arr::get($validatedData, 'user_id'),
            'cookie_id' => Arr::get($validatedData, 'cookie_id'),
        ],
            Arr::except($validatedData, 'design_id'));
        $model->designs()->syncWithoutDetaching([
            $validatedData['design_id'] => ['status' => 1]
        ]);
        return $model->load($relationsToLoad);
    }

    public function getCurrentUserOrGuestCart()
    {
        $cookieId = request()->cookie('cookie_id');
        $userId = auth('sanctum')->id();
        if ($userId || $cookieId) {
            $cart = $this->repository->query()
                ->where(function ($q) use ($cookieId, $userId) {
                    if ($userId) {
                        $q->whereUserId($userId);
                    }
                    elseif ($cookieId) {
                        $q->whereCookieId($cookieId);
                    }
                })
                ->with(['designs.product'])
                ->first();
        }

        return $cart ?? null;
    }

}
