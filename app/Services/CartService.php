<?php

namespace App\Services;

use App\Models\CartItem;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\DesignRepositoryInterface;
use Dflydev\DotAccessData\Data;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CartService extends BaseService
{
    public function __construct(CartRepositoryInterface          $repository,
                                public DesignRepositoryInterface $designRepository,
                                public CartRepositoryInterface   $cartRepository,
    )
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
        $totalPrice = $model->cartItems->isNotEmpty() ? $model->cartItems->sum(fn($value) => $value->pivot->total_price) : 0;
        $model->update(['price' => $totalPrice]);
        $design = $this->designRepository->find($validatedData['design_id']);
        $model->designs()->syncWithoutDetaching([
            $validatedData['design_id'] => [
                'status' => 1,
                'sub_total' => $design->total_price,
                'total_price' => $design->total_price,
            ]
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
                    } elseif ($cookieId) {
                        $q->whereCookieId($cookieId);
                    }
                })
                ->with(['designs.product'])
                ->first();
        }

        return $cart ?? null;
    }

    /**
     * @throws ValidationException
     */
    public function deleteItemFromCart($designId)
    {
        $userId = auth('sanctum')->id();
        $cookieId = request()->cookie('cookie_id');
        if (!$userId && !$cookieId) {
            throw ValidationException::withMessages([
                'authorization' => ['Either a logged-in user or a valid cookie must be provided.'],
            ]);
        }
        $this->handleTransaction(function () use ($designId, $cookieId, $userId) {
            $cart = $this->cartRepository->query()
                ->where(function ($q) use ($cookieId, $userId) {
                    if ($userId) {
                        $q->whereUserId($userId);
                    } else {
                        $q->whereCookieId($cookieId);
                    }
                })->firstOrFail();
            $cartItem = CartItem::where('design_id', $designId)
                ->where('cart_id', $cart->id)
                ->first();
            if (!$cartItem) {
                throw ValidationException::withMessages([
                    'cart' => ['Design item not found in cart.'],
                ]);
            }
            $itemPrice = $cartItem->design->total_price ?? $cartItem->total_price ?? 0;

            $cartItem->delete();
            $cart->update([
                'price' => max(0, $cart->price - $itemPrice),
            ]);
        });
    }


    public function applyDiscount($cartId)
    {
        $subTotal = $this->repository->find($cartId)->designs->pluck('total_price')->sum();
    }
}
