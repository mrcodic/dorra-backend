<?php

namespace App\Services;

use App\Enums\DiscountCode\TypeEnum;
use App\Models\{CartItem, Category, Design, Guest, Product, Template, User};
use App\Repositories\Interfaces\{CartItemRepositoryInterface,
    CategoryRepositoryInterface,
    DiscountCodeRepositoryInterface,
    CartRepositoryInterface,
    DesignRepositoryInterface,
    GuestRepositoryInterface,
    ProductPriceRepositoryInterface,
    ProductRepositoryInterface,
    ProductSpecificationOptionRepositoryInterface,
    ProductSpecificationRepositoryInterface
};
use App\Rules\ValidDiscountCode;
use Illuminate\Support\{Facades\Response, Arr};
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Validation\Rule;
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
        public ProductRepositoryInterface                    $productRepository,
        public CategoryRepositoryInterface                   $categoryRepository,
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
            $design = $request->getDesign();
            $template = $request->getTemplate();

            if ($request->type == \App\Enums\Item\TypeEnum::PRINT->value) {
                if ($request->design_id) {
                    $request->cartable_id = $design->designable_id;
                    $request->cartable_type = $design->designable_type;
                }
                $product = $request->cartable_type === 'App\\Models\\Product'
                    ? $this->productRepository->query()->find($request->cartable_id)
                    : $this->categoryRepository->query()->find($request->cartable_id);


                $priceDetails = $this->calculatePriceDetails($validatedData, $product, $design);

                $cartItem = $cart->addItem(
                    $design ?? $template,
                    \App\Enums\Item\TypeEnum::tryFrom($request->type),
                    Arr::get($priceDetails, 'quantity'),
                    $priceDetails['specs_sum'],
                    $priceDetails['product_price'],
                    $priceDetails['product_price_id'],
                    $priceDetails['sub_total'],
                    $request->cartable_id,
                    $request->cartable_type,
                    $request->color,
                );

                $this->handleSpecs(Arr::get($validatedData, 'specs', []), $cartItem);
            } else {
                $cart->addItem(
                    $design ?? $template,
                    sub_total: $design->price ?? $template->price,
                    type: \App\Enums\Item\TypeEnum::tryFrom($request->type),
                );

            }


            return $cart;
        });
    }

    private function calculatePriceDetails(array $validatedData, $product, $design = null, $price = null): array
    {
        $productPrice = $this->productPriceRepository->query()
            ->find(Arr::get($validatedData, 'product_price_id') ?? $design?->productPrice?->id);
        $productPriceValue = $productPrice?->price ?? $price;
        $specsSum = collect(Arr::get($validatedData, 'specs') ?? $design?->specifications)
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
        $cart = $this->repository->query()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when(!$userId && $guestId, fn($q) => $q->where('guest_id', $guestId))
            ->with([
                'items.cartable' => function (MorphTo $cartable) {
                    $cartable->constrain([
                        Product::class => fn($q) => $q->withLastOfferId()->with('lastOffer'),
                        Category::class => fn($q) => $q->withLastOfferId()->with('lastOffer'),
                    ]);
                },
                'items.itemable' => function ($query) {
                    $query->select(['id', 'name', 'price']);
                },
                'items.itemable.products',
                'items.product.category'
            ])
            ->first();
        if ($cart && $cart->expires_at?->isPast()) {
            $cart->delete();
            return null;
        }
        return $cart;
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
        $message = $this->handleTransaction(function () use ($item, $cart) {
            if ($cart->items()->count() == 1) {
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
        if (!$cart) {
            throw ValidationException::withMessages(['cart' => ['Cart not found for this user.']]);
        }
        $items = $cart->items;
        $hasOffer = $items->contains(function ($item) {
            return (float)$item->cartable->lastOffer?->getRawOriginal('value') > 0;
        });
        if ($hasOffer) {
            throw ValidationException::withMessages(['offer' => ["You can't apply discount when at least one item is offered."]]);

        }
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
        if (!$cart) {
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
        if ($cartItem->cartable->has_custom_prices) {
            $productPrice = $this->productPriceRepository->query()->find($request->product_price_id);
            $updated = $cartItem->update(['product_price' => $productPrice->price,
                'product_price_id' => $productPrice->id,
                'quantity' => $productPrice->quantity]);
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
            ->select(['id', 'cartable_id', 'cartable_type', 'quantity', 'sub_total', 'itemable_id', 'itemable_type', 'product_price'])
            ->findOrFail($itemId)?->load([
                'itemable:id', 'itemable.media', 'product',
                'cartable' => function (MorphTo $cartable) {
                    $cartable->constrain([
                        Product::class => fn($q) => $q->withLastOfferId()->with('lastOffer'),
                        Category::class => fn($q) => $q->withLastOfferId()->with('lastOffer'),
                    ]);
                },
                'cartable.specifications.options', 'specs',
                'itemable', 'specs.productSpecificationOption',
                'specs.productSpecification']);
    }

    public function updatePriceDetails($validatedData, $itemId)
    {
        $message = "Request completed successfully.";
        $cartItem = $this->cartItemRepository->query()
            ->whereKey($itemId)
            ->firstOrFail();


        $product = $cartItem->cartable;
        $priceDetails = $this->calculatePriceDetails($validatedData, $product, price: $cartItem->product_price);

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

    public function checkItem($request)
    {
        $request->validate([
            'cartable_id' => [
                'required',
                Rule::when($request->cartable_type === 'product',
                    Rule::exists('products', 'id')
                ),
                Rule::when($request->cartable_type === 'category',
                    Rule::exists('categories', 'id')
                ),
            ],
            'itemable_id' => [
                'required',
                Rule::when($request->itemable_type === 'template',
                    Rule::exists('templates', 'id')
                ),
                Rule::when($request->itemable_type === 'design',
                    Rule::exists('designs', 'id')
                ),
            ],
            'cartable_type' => ['required', 'in:product,category'],
            'itemable_type' => ['required', 'in:template,design'],
        ]);
        $cartId = auth('sanctum')->user()?->cart?->id ?? Guest::whereCookieValue(request()->cookie('cookie_id'))->first()?->cart?->id;
        $mapItemTypes = [
            'template' => Template::class,
            'design' => Design::class,
        ];
        $mapCartItem = [
            'category' => Category::class,
            'product' => Product::class,
        ];
        return $this->cartItemRepository->query()->where('cart_id', $cartId)
            ->where('cartable_id', $request->cartable_id)
            ->where('cartable_type', $mapCartItem[$request->cartable_type])
            ->when($request->itemable_id, fn($q) => $q->where('itemable_id', $request->itemable_id)
                ->where('itemable_type', $mapItemTypes[$request->itemable_type]))
            ->exists();
    }
}
