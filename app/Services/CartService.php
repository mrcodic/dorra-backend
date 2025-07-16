<?php

namespace App\Services;

use App\Enums\DiscountCode\TypeEnum;
use App\Models\{CartItem, Product};
use App\Repositories\Interfaces\{
    DiscountCodeRepositoryInterface,
    CartRepositoryInterface,
    DesignRepositoryInterface,
    GuestRepositoryInterface
};
use App\Rules\ValidDiscountCode;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CartService extends BaseService
{
    public function __construct(
        CartRepositoryInterface                $repository,
        public DesignRepositoryInterface       $designRepository,
        public CartRepositoryInterface         $cartRepository,
        public DiscountCodeRepositoryInterface $discountCodeRepository,
        public GuestRepositoryInterface        $guestRepository,
    )
    {
        parent::__construct($repository);
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $userId = auth('sanctum')->id();
        $cookieValue = Arr::get($validatedData, 'cookie_id');
        $guestId = null;

        if (!$userId && $cookieValue) {
            $guest = $this->guestRepository->query()
                ->firstOrCreate(['cookie_value' => $cookieValue]);
            $guestId = $guest->id;
        }

        $cart = $this->repository->query()
            ->when($userId, fn($query) => $query->where('user_id', $userId))
            ->when(!$userId && $guestId, fn($query) => $query->where('guest_id', $guestId))
            ->first();

        if (!$cart) {
            $cart = $this->repository->query()->create([
                'user_id' => $userId,
                'guest_id' => $guestId,
                ...Arr::except($validatedData, ['design_id', 'cookie_id']),
            ]);
        }

        $design = $this->designRepository->find($validatedData['design_id']);

        $cart->designs()->syncWithoutDetaching([
            $design->id => [
                'status' => 1,
                'sub_total' => $design->total_price,
                'total_price' => $design->total_price,
            ]
        ]);

        $totalPrice = $cart->designs->sum(fn($design) => $design->total_price ?? 0);
        $cart->update(['price' => getTotalPrice(0, $totalPrice)]);

        return $cart->load(['designs.product']);
    }


    /**
     * @throws ValidationException
     */
    public function getCurrentUserOrGuestCart()
    {
        return $this->resolveUserCart() ?? null;
    }

    private function resolveUserCart()
    {
        $userId = auth('sanctum')->id();
        $cookieValue = request()->cookie('cookie_id');
        $guestId = null;
        if (!$userId && !$guestId) {
            return null;
        }
        if ($cookieValue && !$userId) {
            $guest = $this->guestRepository->query()
                ->where('cookie_value', $cookieValue)
                ->first();
            $guestId = $guest?->id;
        }

        return $this->repository->query()
            ->when($userId, fn($q) =>$q->where('user_id', $userId))
            ->when(!$userId && $guestId, fn($q) => $q->where('guest_id', $guestId))
            ->with(['designs.product', 'cartItems.product'])
            ->first() ?? null;
    }

    /**
     * @throws ValidationException
     */
    public function deleteItemFromCart($designId)
    {
        $this->handleTransaction(function () use ($designId) {
            $cart = $this->resolveUserCart();
            if (!$cart) {
                throw ValidationException::withMessages([
                    'cart' => ['Cart not found for this user.'],
                ]);
            }
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
        $cart->load('cartItems.product');
        $items = $cart->cartItems;

        $discountCode = $this->discountCodeRepository->query()
            ->whereCode($request->code)
            ->firstOrFail();

        $products = $items->pluck('product.id')->filter()->unique();
        $allSameProduct = $products->count() === 1;

        if ($allSameProduct) {
            $product = Product::find($products->first());
        }

        $request->validate([
            'code' => ['required', new ValidDiscountCode($product ?? null)],
        ]);

        $subTotal = $cart->price;
        $discountValue = $discountCode->type == TypeEnum::PERCENTAGE
            ? $discountCode->value
            : round(($discountCode->value / $subTotal) * 100, 2);

        $discountAmount = getDiscountAmount($discountCode, $subTotal);
        $totalPrice = $subTotal - $discountAmount;

        return [
            'discount' => [
                'id' => $discountCode->id,
                'ratio' => $discountValue,
                'value' => $discountAmount,
            ],
            'total_price' => $totalPrice,
        ];
    }

    public function cartInfo(): array
    {

        $cart = $this->resolveUserCart();
        return [
            'price' => $cart?->price ?? 0,
            'items_count' => $cart?->cartItems->count() ?? 0,
        ];
    }
}
