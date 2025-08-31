<?php

namespace App\Services;

use App\Enums\DiscountCode\TypeEnum;
use App\Models\{Category, Guest, Product, User};
use App\Repositories\Interfaces\{CartItemRepositoryInterface,
    DiscountCodeRepositoryInterface,
    CartRepositoryInterface,
    DesignRepositoryInterface,
    GuestRepositoryInterface,
    ProductPriceRepositoryInterface,
    ProductSpecificationOptionRepositoryInterface,
    ProductSpecificationRepositoryInterface
};
use App\Rules\ValidDiscountCode;
use Illuminate\Support\{Facades\Response, Arr};
use Illuminate\Validation\ValidationException;


class CartService extends BaseService
{
    public function __construct(
        CartRepositoryInterface                              $repository,
        public DesignRepositoryInterface                     $designRepository,
        public CartRepositoryInterface                       $cartRepository,
        public DiscountCodeRepositoryInterface               $discountCodeRepository,
        public GuestRepositoryInterface                      $guestRepository,
        public ProductPriceRepositoryInterface               $productPriceRepository,
        public ProductSpecificationOptionRepositoryInterface $optionRepository,
        public ProductSpecificationRepositoryInterface       $specificationRepository,
        public CartItemRepositoryInterface                   $cartItemRepository,
    )
    {
        parent::__construct($repository);
    }

    public function storeResource($request, $relationsToStore = [], $relationsToLoad = [])
    {
        return $this->handleTransaction(function () use ($request, $relationsToStore, $relationsToLoad) {
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

            $priceDetails = $this->calculatePriceDetails($validatedData, $product);

            $cartItem = $cart->addItem(
                $design ?? $template,
                Arr::get($priceDetails, 'quantity'),
                $priceDetails['specs_sum'],
                $priceDetails['product_price'],
                $priceDetails['product_price_id'],
                $priceDetails['sub_total'],
                $product,
            );

            $this->handleSpecs(Arr::get($validatedData, 'specs', []), $cartItem);

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
            ->with([
                'items.product','items.itemable' => function ($query) {
                $query->select(['id','name']);
            },'items.itemable.products'])
            ->first();
    }

    public function deleteItemFromCart($itemId)
    {
        $message = "Item removed from cart successfully.";
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
        $message =  $this->handleTransaction(function () use ($item, $cart) {
            if ($cart->items()->count() == 1)
            {
                $cart->update([
                    'discount_code_id' => null,
                    'discount_amount' => 0,
                ]);
            }
            if ($cart->price - $item->sub_total < $cart->discount_amount) {
                $cart->update([
                    'discount_code_id' => null,
                    'discount_amount' => 0,
                    'price' => $cart->items()->sum('sub_total'),
                ]);
               return "The item has been removed from your cart. Since the cart total is now lower, the discount code is no longer valid.";

            }
            $item->delete();
            $cart->update([
                'discount_code_id' => null,
                'discount_amount' => 0,
                'price' => $cart->items()->sum('sub_total'),
            ]);

        });
        return $message;
    }


    public function applyDiscount($request)
    {
        $cart = $this->resolveUserCart();
        if(!$cart)
        {
            throw ValidationException::withMessages(['cart' => ['Cart not found for this user.']]);
        }
        $items = $cart->load('items.product.category')->items;
        $products = $items->pluck('product.id')->filter()->unique();
        $categories = $items->pluck('product.category.id')->filter()->unique();
        $allSameProduct = $products->count() === 1;
        $allSameCategory = $categories->count() === 1;
        $product = $allSameProduct ? $items->first()->product : null;
        $category = $allSameCategory ? $items->first()->product->category : null;
        $request->validate([
            'code' => ['required', new ValidDiscountCode($product, $category, $cart)],
        ]);
        $discountCode = $this->discountCodeRepository->query()
            ->whereCode($request->code)
            ->firstOrFail();

        $cart->update([
            'discount_code_id' => $discountCode->id,
            'discount_amount' => getDiscountAmount($discountCode, $cart->price),
        ]);

        return [
            'code' => $discountCode?->code,
            'ratio' => $cart->price
                ? (
                    ($discountCode?->type === TypeEnum::PERCENTAGE
                        ? $discountCode?->value * 100
                        : ($discountCode?->value / $cart->price) * 100
                    ) . '%'
                )
                : '0%',
            'value' => getDiscountAmount($discountCode, $cart->price) ?? 0,
        ];
    }
   public function removeDiscount(): void
   {
        $cart = $this->resolveUserCart();
        if(!$cart)
        {
            throw ValidationException::withMessages(['cart' => ['Cart not found for this user.']]);
        }
        $cart->update([
            'discount_code_id' => null,
            'discount_amount' => 0,
        ]);
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
        $message = "Request completed successfully.";
        $cartItem = $this->cartItemRepository->find($id);
        if ($cartItem->product->has_custom_prices) {
            $productPrice = $this->productPriceRepository->query()->find($request->product_price_id);
            $updated = $cartItem->update(['product_price' => $productPrice->price, 'quantity' => $productPrice->quantity]);
        } else {
            $updated = $cartItem->update($request->only(['quantity']));
        }
        if ($cartItem->cart->price <= $cartItem->cart->discount_amount) {
            $cartItem->cart->update([
                'discount_code_id' => null,
                'discount_amount' => 0,
            ]);
            $message = "Since the cart total is now lower, the discount code is no longer valid.";
        }
        return $message;
    }

    public function priceDetails($itemId)
    {
        return $this->cartItemRepository->query()
            ->select(['id', 'product_id', 'quantity', 'sub_total','itemable_id', 'itemable_type'])
            ->findOrFail($itemId)?->load([
                'itemable:id','itemable.media','product',
                'product.specifications.options', 'specs',
                'itemable','specs.productSpecificationOption',
                'specs.productSpecification']);
    }

    public function updatePriceDetails($validatedData, $itemId)
    {
        $message = "Request completed successfully.";
        $cartItem = $this->cartItemRepository->query()
            ->select(['id', 'product_id', 'quantity', 'sub_total', 'product_price', 'cart_id'])
            ->find($itemId);

        $product = $cartItem->product;
        $priceDetails = $this->calculatePriceDetails($validatedData, $product, $cartItem->product_price);

        $cartItem->update([
            'sub_total' => $priceDetails['sub_total'],
            'specs_price' => $priceDetails['specs_sum'],
            'product_price' => $priceDetails['product_price'],
        ]);
        if ($cartItem->cart->price <= $cartItem->cart->discount_amount) {
            $cartItem->cart->update([
                'discount_code_id' => null,
                'discount_amount' => 0,
            ]);
            $message = "Since the cart total is now lower, the discount code is no longer valid.";
        }
        $this->handleSpecs(Arr::get($validatedData, 'specs', []), $cartItem);
        return [$message, $cartItem];
    }

    private function calculatePriceDetails(array $validatedData, $product, $price = null): array
    {
        $productPrice = $this->productPriceRepository->query()
            ->find(Arr::get($validatedData, 'product_price_id'));

        $productPriceValue = $productPrice?->price ?? $price;
        $specsSum = collect(Arr::get($validatedData, 'specs'))
            ->map(function ($spec) {
                return $this->optionRepository->query()->find($spec['option'])?->price ?? 0;
            })->sum();
        $basePrice = $product->base_price ?? $productPriceValue;

        $subTotal = ($product->base_price ?? $productPriceValue) + $specsSum;
        $calculatePrices = [
            'product_price' => $basePrice,
            'specs_sum' => $specsSum,
            'sub_total' => $subTotal,
            'product_price_id' => $productPrice?->id,
        ];
        $quantity = $productPrice?->quantity;
        if ($quantity) {
            $calculatePrices['quantity'] = $quantity;
        }

        return $calculatePrices;
    }

    private function handleSpecs(array $specs, $cartItem): void
    {
        $syncData = collect($specs)
            ->map(function ($spec) use ($cartItem) {
                $option = $this->optionRepository->query()->find($spec['option']);
                $specification = $this->specificationRepository->query()->find($spec['id']);

                if ($option && $specification) {
                    return [
                        'cart_item_id' => $cartItem->id,
                        'product_specification_id' => $specification->id,
                        'spec_option_id' => $option->id,
                    ];
                }

                return null;
            })
            ->filter()
            ->values();

        $cartItem->specs()->delete();
        $cartItem->specs()->insert($syncData->toArray());
    }


}
