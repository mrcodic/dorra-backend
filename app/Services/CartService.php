<?php

namespace App\Services;


use App\Repositories\Interfaces\CartRepositoryInterface;

use Illuminate\Support\Arr;

class CartService extends BaseService
{
    public function __construct(CartRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad =[])
    {
        $model = $this->repository->query()->firstOrCreate(Arr::except($validatedData,'design_id'));
        $model->designs()->attach([
            $validatedData['design_id'] => ['status' => 1]
        ]);
        return $model->load($relationsToLoad);
    }

    public function getCurrentUserOrGuestCart()
    {
        $cookieId = request()->cookie('cookie_id');
        $userId = request()->user()?->id;

        $cart = $this->repository->query()
            ->where(function ($q) use ($cookieId, $userId) {
                if ($userId) {
                    $q->whereUserId($userId);
                }
                if ($cookieId) {
                    $q->whereCookieId($cookieId);
                }
            })
            ->with('designs')
            ->first();
        return $cart ?? ['designs' => []];
    }

}
