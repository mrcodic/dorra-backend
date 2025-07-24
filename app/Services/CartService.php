<?php

namespace App\Services;

use App\Enums\DiscountCode\TypeEnum;
use App\Models\{Guest, Product, User};
use App\Repositories\Interfaces\{CartItemRepositoryInterface,
    DiscountCodeRepositoryInterface,
    CartRepositoryInterface,
    DesignRepositoryInterface,
    GuestRepositoryInterface,
    ProductPriceRepositoryInterface,
    ProductSpecificationOptionRepositoryInterface,};
use App\Rules\ValidDiscountCode;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;


class CartService extends BaseService
{
    public function __construct(
        CartRepositoryInterface                $repository,
        public DesignRepositoryInterface       $designRepository,
        public CartRepositoryInterface         $cartRepository,
        public DiscountCodeRepositoryInterface $discountCodeRepository,
        public GuestRepositoryInterface        $guestRepository,
        public ProductPriceRepositoryInterface $productPriceRepository,
        public ProductSpecificationOptionRepositoryInterface $optionRepository,
        public CartItemRepositoryInterface $cartItemRepository,
    )
    {
        parent::__construct($repository);
    }

    public function storeResource($request, $relationsToStore = [], $relationsToLoad = [])
    {
      return  $this->handleTransaction(function () use ($request, $relationsToStore, $relationsToLoad) {
            $validatedData = $request->validated();
            $userId = getAuthOrGuest() instanceof User ? getAuthOrGuest()->id : null;
            $guestId = getAuthOrGuest() instanceof Guest ? getAuthOrGuest()->id : null;
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
            $product = $request->getProduct();
            $template = $request->getTemplate();
            $design = $request->getDesign();
            $productPrice = $this->productPriceRepository->query()->find(Arr::get($validatedData,'product_price_id'))?->price;
            $specsSum = collect(Arr::get($validatedData, 'specs'))
                ->map(function ($spec) {
                    return $this->optionRepository->query()->find($spec['option'])?->price ?? 0;
                })
                ->sum();
            $quantity = $productPrice->quantity ?? 1;
            $productPrice = $product->base_price ?? $productPrice;
            $subTotal = ($product->base_price ?? $productPrice )+ $specsSum;
            $cartItem = $cart->addItem($design ?? $template,$quantity, $specsSum, $productPrice,$subTotal,$product);
            collect(Arr::get($validatedData, 'specs'))->each(function ($spec) use ($cartItem) {
              $option = $this->optionRepository->query()->find($spec['option']);
              $specification = $this->optionRepository->query()->find($spec['id']);
              if ($option && $specification) {
                  $cartItem->specs()->create([
                      'spec_name' => $specification->name,
                      'option_name' => $option->value,
                      'option_price' => $option->price,
                  ]);
              }
          });


          return $cart;
        });

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
        if ($cookieValue && !$userId) {
            $guest = $this->guestRepository->query()
                ->where('cookie_value', $cookieValue)
                ->first();
            $guestId = $guest?->id;
        }
        if (!$userId && !$guestId) {
            return null;
        }

        return $this->repository->query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when(!$userId && $guestId, fn($q) => $q->where('guest_id', $guestId))
//            ->with(['designs.product', 'cartItems.product', 'designs' => fn($q) => $q->latest(),])
                ->with(['items.product'])
            ->first();
    }

    public function deleteItemFromCart($itemId)
    {
        $cart = $this->resolveUserCart();
        if (!$cart) {
            throw ValidationException::withMessages([
                'cart' => ['Cart not found for this user.'],
            ]);
        }
        $item = $cart->items()->whereKey($itemId)->first();

        if (!$item) {
            throw ValidationException::withMessages([
                'cart' => ['Item not found in cart.'],
            ]);
        }
        $item->delete();
        $cart->update([
            'price' => $cart->items()->sum('sub_total'),
        ]);

        return Response::api(message:"Item removed from cart successfully.");

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
            : ($discountCode->value / $subTotal) * 100;

        $discountAmount = getDiscountAmount($discountCode, $subTotal);
        $totalPrice = $subTotal - $discountAmount;

        return [
            'discount' => [
                'id' => $discountCode->id,
                'ratio' => number_format($discountValue, 2, '.', ''),
                'value' => number_format($discountAmount, 2, '.', ''),
            ],
            'total_price' => number_format($totalPrice, 2, '.', ''),
        ];

    }

    public function cartInfo(): array
    {
        $cart = $this->resolveUserCart();
        return [
            'price' => $cart?->price ?? 0,
            'items_count' => $cart?->totalItems() ?? 0,
        ];
    }

    public function addQuantity($request, $id)
    {
        $cartItem = $this->cartItemRepository->find($id);
        if ($cartItem->product->has_custom_prices) {
            dd($request->only(['product_price_id']));
//            $productPrice = $this->productPriceRepository->query()->find([$request->only(['product_price_id']]))?->price;
            $updated = $cartItem->update($productPrice);
        } else {
            $updated = $cartItem->update($request->only(['quantity']));
        }
        return $updated;
    }
}
