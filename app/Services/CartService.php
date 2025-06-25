<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\DesignRepositoryInterface;
use App\Repositories\Interfaces\DiscountCodeRepositoryInterface;
use App\Rules\ValidDiscountCode;
use Dflydev\DotAccessData\Data;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CartService extends BaseService
{
    public function __construct(CartRepositoryInterface                $repository,
                                public DesignRepositoryInterface       $designRepository,
                                public CartRepositoryInterface         $cartRepository,
                                public DiscountCodeRepositoryInterface $discountCodeRepository,
    )
    {
        parent::__construct($repository);
    }

    private function resolveUserCart()
    {
        $userId = auth('sanctum')->id();
        $cookieId = request()->cookie('cookie_id');

        if (!$userId && !$cookieId) {
            throw ValidationException::withMessages([
                'authorization' => ['Either a logged-in user or a valid cookie must be provided.'],
            ]);
        }
        return $this->repository->query()
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
    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $model = $this->repository->query()->firstOrCreate([
            'user_id' => Arr::get($validatedData, 'user_id'),
            'cookie_id' => Arr::get($validatedData, 'cookie_id'),
        ],
            Arr::except($validatedData, 'design_id'));
        $design = $this->designRepository->find($validatedData['design_id']);
        $totalPrice = $model->cartItems->isNotEmpty() ? $model->cartItems->sum(fn($value) => $value->pivot->total_price) : $design->total_price;
        $model->update(['price' => $totalPrice]);
        $model->designs()->syncWithoutDetaching([
            $validatedData['design_id'] => [
                'status' => 1,
                'sub_total' => $design->total_price,
                'total_price' => $design->total_price,
            ]
        ]);
        return $model->load($relationsToLoad);
    }

    /**
     * @throws ValidationException
     */
    public function getCurrentUserOrGuestCart()
    {
        $cart = $this->resolveUserCart();
        return $cart ?? null;
    }

    /**
     * @throws ValidationException
     */
    public function deleteItemFromCart($designId)
    {
        $this->handleTransaction(function () use ($designId) {
            $cart = $this->resolveUserCart();
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


    public function applyDiscount($request)
    {
        $cart = $this->resolveUserCart();
        $items = $cart->cartItems;
        $products = $items->map(function ($item) {
            return optional($item?->load('product')->product)->id;
        })->filter()->unique();
        $allSameProduct = $products->count() === 1;
        if ($allSameProduct) {
            $request->validate(['code' => ['required', new ValidDiscountCode(Product::find($products[0]))]]);
        } else {
            $request->validate(['code' => ['required', new ValidDiscountCode()]]);
        }
        $subTotal =$cart->price;
        $discountValue = $this->discountCodeRepository->query()->where(['code' => $request->code])->value('value');
        $discountAmount = getDiscountAmount($discountValue,$subTotal);
        $totalPrice = getTotalPrice($discountValue,$subTotal);
        return [
            'discount' => [
                'ratio' => $discountValue,
                'value' => $discountAmount,
            ],
            'total_price' => $totalPrice,
        ];

    }

    public function cartInfo()
    {
        $cart = $this->resolveUserCart();
        return [
            'price' => $cart->price,
            'items_count' => $cart->cartItems->count(),
        ];

    }
}
