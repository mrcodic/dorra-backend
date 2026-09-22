<?php

namespace App\Services\Bundle;

use App\Enums\Item\TypeEnum;
use App\Models\Bundle;
use App\Models\BundleItem;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Design;
use App\Models\Guest;
use App\Models\Media;
use App\Models\Mockup;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ProductSpecification;
use App\Models\ProductSpecificationOption;
use App\Models\Template;
use App\Models\User;
use App\Repositories\Interfaces\CartRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BundleCartService
{
    public function __construct(
        public CartRepositoryInterface $cartRepository,
    ) {}

    public function store(Request $request): Cart
    {
        return DB::transaction(function () use ($request) {
            $authOrGuest = getAuthOrGuest();

            $userId = $authOrGuest instanceof User ? $authOrGuest->id : null;
            $guestId = $authOrGuest instanceof Guest ? $authOrGuest->id : null;

            if (! $userId && ! $guestId) {
                throw ValidationException::withMessages([
                    'cart' => ['Cart could not be resolved.'],
                ]);
            }

            $cart = $this->cartRepository->query()
                ->when($userId, fn ($query) => $query->where('user_id', $userId))
                ->when(! $userId && $guestId, fn ($query) => $query->where('guest_id', $guestId))
                ->first();

            if (! $cart) {
                $cart = $this->cartRepository->query()->create([
                    'user_id' => $userId,
                    'guest_id' => $guestId,
                ]);
            }

            $this->ensureCartCanAcceptBundle($cart);

            $bundle = $this->getValidBundle((int) $request->bundle_id);

            /*
             * groupBy allows same bundle_item_id to be sent more than once.
             *
             * Supported:
             * 1) Old flow:
             *    one item without quantity => full bundle_item quantity
             *
             * 2) New flow:
             *    same bundle_item_id repeated with different template/design
             *    or explicit quantity per row.
             */
            $payloadItems = collect($request->input('items', []))
                ->groupBy(fn ($item) => (int) Arr::get($item, 'bundle_item_id'));

            $bundleGroupKey = (string) Str::uuid();

            $bundleItems = collect([$bundle->trigger])
                ->merge($bundle->rewards)
                ->filter()
                ->values();

            $this->ensureBundleItemsAreNotAlreadyInCart(
                cart: $cart,
                bundle: $bundle,
                bundleItems: $bundleItems
            );

            foreach ($bundleItems as $bundleItem) {
                $requiredQuantity = max((int) ($bundleItem->quantity ?? 1), 1);
                $rawConfigs = $payloadItems
                    ->get($bundleItem->id, collect())
                    ->values();

                $this->ensureCustomPriceBundleItemHasSingleConfig(
                    bundleItem: $bundleItem,
                    configs: $rawConfigs
                );

                $configs = $this->normalizeBundleItemConfigs(
                    configs: $rawConfigs,
                    requiredQuantity: $requiredQuantity
                );

                if ($configs->isEmpty()) {
                    throw ValidationException::withMessages([
                        'items' => ["Missing configuration for bundle item #{$bundleItem->id}."],
                    ]);
                }

                $sentQuantity = $configs->sum(function ($config) {
                    return max((int) Arr::get($config, 'quantity', 1), 1);
                });

                if ($sentQuantity !== $requiredQuantity) {
                    throw ValidationException::withMessages([
                        'items' => [
                            "Bundle item #{$bundleItem->id} requires quantity {$requiredQuantity}, but {$sentQuantity} was sent.",
                        ],
                    ]);
                }

                $cartable = $bundleItem->itemable;

                if (! $cartable instanceof Product && ! $cartable instanceof Category) {
                    throw ValidationException::withMessages([
                        'bundle_item' => ["Invalid bundle item #{$bundleItem->id}."],
                    ]);
                }

                foreach ($configs as $config) {
                    $configQuantity = max((int) Arr::get($config, 'quantity', 1), 1);

                    $itemable = $this->resolveItemable($config, $cartable);

                    $priceDetails = $this->calculatePriceDetails(
                        config: $config,
                        cartable: $cartable,
                        itemable: $itemable,
                        bundleItem: $bundleItem
                    );

                    $priceDetails = $this->normalizeQuantityPriceDetails(
                        priceDetails: $priceDetails,
                        requiredQuantity: $requiredQuantity,
                        configQuantity: $configQuantity
                    );

                    $discountAmount = $this->isReward($bundleItem)
                        ? $this->calculateRewardDiscount($bundleItem, $priceDetails['sub_total'])
                        : 0;

                    $cartItem = $cart->items()->create([
                        'itemable_id' => $itemable->id,
                        'itemable_type' => get_class($itemable),

                        'cartable_id' => $cartable->id,
                        'cartable_type' => get_class($cartable),

                        'specs_price' => $priceDetails['specs_sum'],
                        'product_price' => $priceDetails['product_price'],
                        'product_price_id' => $priceDetails['product_price_id'],

                        'sub_total' => $priceDetails['sub_total'],
                        'quantity' => $configQuantity,

                        'color' => Arr::get($config, 'color'),
                        'type' => TypeEnum::PRINT,

                        'discount_code_id' => null,
                        'discount_amount' => $discountAmount,

                        'bundle_id' => $bundle->id,
                        'bundle_item_id' => $bundleItem->id,
                        'bundle_group_key' => $bundleGroupKey,
                        'bundle_role' => $this->getBundleItemRole($bundleItem),
                    ]);

                    $this->handleSpecs(
                        specs: $this->resolveSpecs($config, $itemable),
                        cartItem: $cartItem
                    );

                    $this->attachMockupMedia(
                        config: $config,
                        cart: $cart,
                        cartItem: $cartItem,
                        cartable: $cartable,
                        itemable: $itemable
                    );
                }
            }

            $cart->update([
                'discount_code_id' => null,
                'discount_amount' => 0,
                'price' => $cart->items()->sum('sub_total'),
            ]);

            return $cart->fresh([
                'items.bundle',
                'items.bundleItem',
                'items.itemable',
                'items.cartable',
                'items.specs',
                'discountCode',
            ]);
        });
    }
    private function ensureCustomPriceBundleItemHasSingleConfig(BundleItem $bundleItem, $configs): void
    {
        if (! $this->bundleItemUsesCustomPrice($bundleItem)) {
            return;
        }

        if (collect($configs)->count() <= 1) {
            return;
        }

        throw ValidationException::withMessages([
            'items' => [
                "Bundle item #{$bundleItem->id} uses custom price quantity. Please choose one template/design only.",
            ],
        ]);
    }

    private function bundleItemUsesCustomPrice(BundleItem $bundleItem): bool
    {
        $item = $bundleItem->itemable;

        if ($bundleItem->price_id) {
            return true;
        }

        if ((bool) data_get($item, 'has_custom_prices')) {
            return true;
        }

        return $item && method_exists($item, 'prices') && $item->prices()->exists();
    }
    private function normalizeBundleItemConfigs($configs, int $requiredQuantity)
    {
        $configs = collect($configs)->values();

        if ($configs->isEmpty()) {
            return $configs;
        }

        $hasAnyQuantity = $configs->contains(function ($config) {
            $quantity = Arr::get($config, 'quantity');

            return $quantity !== null && $quantity !== '';
        });

        /*
         * Old flow:
         * one config without quantity means full required bundle item quantity.
         */
        if (! $hasAnyQuantity && $configs->count() === 1) {
            $configs = $configs->map(function ($config) use ($requiredQuantity) {
                $config['quantity'] = $requiredQuantity;

                return $config;
            });
        } else {
            /*
             * Multiple configs:
             * missing quantity means quantity = 1.
             */
            $configs = $configs->map(function ($config) {
                $config['quantity'] = max((int) Arr::get($config, 'quantity', 1), 1);

                return $config;
            });
        }

        /*
         * Merge duplicated same template/design/specs/color/mockup
         * for the same bundle_item_id.
         */
        return $configs
            ->groupBy(fn ($config) => $this->bundleConfigUniqueKey($config))
            ->map(function ($sameConfigs) {
                $first = $sameConfigs->first();

                $first['quantity'] = $sameConfigs->sum(function ($config) {
                    return max((int) Arr::get($config, 'quantity', 1), 1);
                });

                return $first;
            })
            ->values();
    }

    private function bundleConfigUniqueKey(array $config): string
    {
        $specs = collect(Arr::get($config, 'specs', []))
            ->map(fn ($spec) => [
                'id' => (int) Arr::get($spec, 'id'),
                'option' => (int) Arr::get($spec, 'option'),
            ])
            ->sortBy('id')
            ->values()
            ->toJson();

        return implode('|', [
            Arr::get($config, 'template_id') ?: 'template:null',
            Arr::get($config, 'design_id') ?: 'design:null',
            Arr::get($config, 'color') ?: 'color:null',
            Arr::get($config, 'mockup_id') ?: 'mockup:null',
            $specs,
        ]);
    }

    private function normalizeQuantityPriceDetails(
        array $priceDetails,
        int $requiredQuantity,
        int $configQuantity
    ): array {
        $productPrice = (float) ($priceDetails['product_price'] ?? 0);
        $specsSum = (float) ($priceDetails['specs_sum'] ?? 0);

        /*
         * Dorra custom price is usually package price.
         * Example:
         * price option = EGP 400 for quantity 20.
         *
         * If user chooses:
         * template A quantity 10
         * template B quantity 10
         *
         * each unit should be 400 / 20 = 20.
         */
        if (! empty($priceDetails['product_price_id']) && $requiredQuantity > 1) {
            $productPrice = round($productPrice / $requiredQuantity, 2);
        }

        $priceDetails['product_price'] = $productPrice;
        $priceDetails['quantity'] = $configQuantity;
        $priceDetails['sub_total'] = round(
            ($productPrice + $specsSum) * $configQuantity,
            2
        );

        return $priceDetails;
    }

    private function ensureBundleItemsAreNotAlreadyInCart(
        Cart $cart,
        Bundle $bundle,
        $bundleItems
    ): void {
        /*
         * Prevent adding the exact same bundle twice.
         */
        $sameBundleAlreadyExists = $cart->items()
            ->where('bundle_id', $bundle->id)
            ->whereNotNull('bundle_group_key')
            ->exists();

        if ($sameBundleAlreadyExists) {
            throw ValidationException::withMessages([
                'bundle_id' => ['This bundle is already added to cart.'],
            ]);
        }

        /*
         * Prevent conflict with normal cart items only.
         *
         * Same product inside another bundle group is allowed,
         * because each bundle has its own bundle_group_key.
         */
        foreach ($bundleItems as $bundleItem) {
            $cartable = $bundleItem->itemable;

            if (! $cartable instanceof Product && ! $cartable instanceof Category) {
                continue;
            }

            $cartableTypes = array_unique([
                get_class($cartable),
                $cartable->getMorphClass(),
            ]);

            $alreadyExistsAsNormalItem = $cart->items()
                ->whereIn('cartable_type', $cartableTypes)
                ->where('cartable_id', $cartable->getKey())
                ->where(function ($query) {
                    $query
                        ->whereNull('bundle_group_key')
                        ->orWhere('bundle_group_key', '');
                })
                ->exists();

            if ($alreadyExistsAsNormalItem) {
                throw ValidationException::withMessages([
                    'bundle_id' => [
                        "Item [{$cartable->name}] is already in cart. Remove it first before adding this bundle.",
                    ],
                ]);
            }
        }
    }

    private function getValidBundle(int $bundleId): Bundle
    {
        $bundle = Bundle::query()
            ->with([
                'trigger.itemable',
                'rewards.itemable',
            ])
            ->findOrFail($bundleId);

        $status = $bundle->status?->value ?? $bundle->status;

        if ($status !== 'active') {
            throw ValidationException::withMessages([
                'bundle_id' => ['Selected bundle is not active.'],
            ]);
        }

        if ($bundle->start_at && $bundle->start_at->isFuture()) {
            throw ValidationException::withMessages([
                'bundle_id' => ['Selected bundle is not started yet.'],
            ]);
        }

        if ($bundle->end_at && $bundle->end_at->isPast()) {
            throw ValidationException::withMessages([
                'bundle_id' => ['Selected bundle is expired.'],
            ]);
        }

        if (! $bundle->trigger || $bundle->rewards->isEmpty()) {
            throw ValidationException::withMessages([
                'bundle_id' => ['Selected bundle is not configured correctly.'],
            ]);
        }

        /*
         * Prevent this invalid setup:
         *
         * Bundle Item 1: notebook quantity 20
         * Bundle Item 2: notebook quantity 1 discount 50%
         *
         * If same Product/Category is needed more than once,
         * use one bundle item with the required quantity,
         * then split templates/designs from request payload.
         */
        $this->ensureBundleHasNoDuplicateItems($bundle);

        return $bundle;
    }

    private function ensureBundleHasNoDuplicateItems(Bundle $bundle): void
    {
        $bundleItems = collect([$bundle->trigger])
            ->merge($bundle->rewards)
            ->filter()
            ->values();

        $duplicates = $bundleItems
            ->groupBy(function ($bundleItem) {
                $itemable = $bundleItem->itemable;

                return implode('|', [
                    $itemable ? get_class($itemable) : 'null',
                    $itemable?->getKey() ?? 'null',
                ]);
            })
            ->filter(fn ($items) => $items->count() > 1);

        if ($duplicates->isEmpty()) {
            return;
        }

        $duplicateNames = $duplicates
            ->map(function ($items) {
                $first = $items->first();

                return $first?->itemable?->name ?? "Bundle item #{$first?->id}";
            })
            ->values()
            ->implode(', ');

        throw ValidationException::withMessages([
            'bundle_id' => [
                "This bundle has duplicated items: {$duplicateNames}. Please keep one bundle item and set the correct quantity.",
            ],
        ]);
    }

    private function ensureCartCanAcceptBundle(Cart $cart): void
    {
        $hasDiscountCode =
            $cart->discount_code_id ||
            $cart->items()->whereNotNull('discount_code_id')->exists();

        if ($hasDiscountCode) {
            throw ValidationException::withMessages([
                'bundle' => ['Remove discount code before adding bundle.'],
            ]);
        }
    }

    private function resolveItemable(array $config, Model $cartable): Design|Template
    {
        $designId = Arr::get($config, 'design_id');
        $templateId = Arr::get($config, 'template_id');

        if ($designId) {
            $design = Design::query()
                ->with(['specifications', 'mockup', 'products'])
                ->find($designId);

            if (! $design) {
                throw ValidationException::withMessages([
                    'design_id' => ['Selected design not found.'],
                ]);
            }

            $this->validateDesignMatchesCartable($design, $cartable);

            return $design;
        }

        if ($templateId) {
            $template = Template::query()
                ->with(['products', 'categories', 'media'])
                ->find($templateId);

            if (! $template) {
                throw ValidationException::withMessages([
                    'template_id' => ['Selected template not found.'],
                ]);
            }

            $this->validateTemplateMatchesCartable($template, $cartable);

            return $template;
        }

        throw ValidationException::withMessages([
            'template_id' => ['Template or design is required for every bundle item.'],
        ]);
    }

    private function validateTemplateMatchesCartable(Template $template, Model $cartable): void
    {
        if ($cartable instanceof Product) {
            $exists = $template->products->contains('id', $cartable->id);

            if (! $exists) {
                throw ValidationException::withMessages([
                    'template_id' => ['Selected template is not associated with selected product.'],
                ]);
            }

            return;
        }

        if ($cartable instanceof Category) {
            $exists = $template->categories->contains('id', $cartable->id);

            if (! $exists) {
                throw ValidationException::withMessages([
                    'template_id' => ['Selected template is not associated with selected category.'],
                ]);
            }
        }
    }

    private function validateDesignMatchesCartable(Design $design, Model $cartable): void
    {
        if (! $design->designable_id || ! $design->designable_type) {
            return;
        }

        if (
            (int) $design->designable_id !== (int) $cartable->id ||
            $design->designable_type !== get_class($cartable)
        ) {
            throw ValidationException::withMessages([
                'design_id' => ['Selected design does not match bundle item.'],
            ]);
        }
    }

    private function calculatePriceDetails(
        array $config,
        Model $cartable,
        Design|Template $itemable,
        BundleItem $bundleItem
    ): array {
        $productPrice = $this->resolveProductPrice($cartable, $bundleItem);

        $basePrice = $productPrice?->price
            ?? data_get($cartable, 'base_price')
            ?? data_get($itemable, 'price')
            ?? 0;

        $specsSum = $this->calculateSpecsSum(
            specs: $this->resolveSpecs($config, $itemable)
        );

        $quantity = (int) ($productPrice?->quantity ?? $bundleItem->quantity ?? 1);

        return [
            'product_price' => (float) $basePrice,
            'specs_sum' => (float) $specsSum,
            'sub_total' => (float) $basePrice + (float) $specsSum,
            'product_price_id' => $productPrice?->id,
            'quantity' => $quantity,
        ];
    }

    private function resolveProductPrice(Model $cartable, BundleItem $bundleItem): ?ProductPrice
    {
        $hasPrices = method_exists($cartable, 'prices')
            && $cartable->prices()->exists();

        if (! $hasPrices) {
            return null;
        }

        if (! $bundleItem->price_id) {
            throw ValidationException::withMessages([
                'bundle_item' => ["Bundle item #{$bundleItem->id} must have selected price option."],
            ]);
        }

        $price = $cartable->prices()
            ->whereKey($bundleItem->price_id)
            ->first();

        if (! $price) {
            throw ValidationException::withMessages([
                'bundle_item' => ["Selected price option does not belong to bundle item #{$bundleItem->id}."],
            ]);
        }

        return $price;
    }

    private function resolveSpecs(array $config, Design|Template $itemable): array
    {
        $requestSpecs = Arr::get($config, 'specs', []);

        if (! empty($requestSpecs)) {
            return $requestSpecs;
        }

        if ($itemable instanceof Design) {
            return $itemable->specifications
                ->map(function ($specification) {
                    return [
                        'id' => $specification->id,
                        'option' => $specification->pivot->option_id,
                    ];
                })
                ->toArray();
        }

        return [];
    }

    private function calculateSpecsSum(array $specs): float
    {
        return collect($specs)
            ->map(function ($spec) {
                $optionId = Arr::get($spec, 'option');

                return ProductSpecificationOption::query()
                    ->find($optionId)
                    ?->price ?? 0;
            })
            ->sum();
    }

    private function handleSpecs(array $specs, CartItem $cartItem): void
    {
        if (empty($specs)) {
            return;
        }

        $syncData = collect($specs)
            ->map(function ($spec) use ($cartItem) {
                $specification = ProductSpecification::query()
                    ->find(Arr::get($spec, 'id'));

                $option = ProductSpecificationOption::query()
                    ->find(Arr::get($spec, 'option'));

                if (! $specification || ! $option) {
                    return null;
                }

                return [
                    'cart_item_id' => $cartItem->id,
                    'product_specification_id' => $specification->id,
                    'spec_option_id' => $option->id,
                ];
            })
            ->filter()
            ->values()
            ->toArray();

        if (! empty($syncData)) {
            $cartItem->specs()->insert($syncData);
        }
    }

    private function calculateRewardDiscount(BundleItem $bundleItem, float $subTotal): float
    {
        if ($subTotal <= 0) {
            return 0;
        }

        $discountType = $bundleItem->discount_type?->value ?? $bundleItem->discount_type;

        if ($discountType === 'free') {
            $discountAmount = $subTotal;
        } elseif ($discountType === 'percentage') {
            $discountAmount = $subTotal * ((float) $bundleItem->discount_value / 100);
        } else {
            $discountAmount = 0;
        }

        if ($bundleItem->max_discount_amount !== null) {
            $discountAmount = min($discountAmount, (float) $bundleItem->max_discount_amount);
        }

        return min($discountAmount, $subTotal);
    }

    private function attachMockupMedia(
        array $config,
        Cart $cart,
        CartItem $cartItem,
        Model $cartable,
        Design|Template $itemable
    ): void {
        if (! $itemable instanceof Template) {
            return;
        }

        $mockupId = Arr::get($config, 'mockup_id');

        if (! $mockupId) {
            return;
        }

        $categoryId = $cartable instanceof Product
            ? $cartable->category_id
            : $cartable->id;

        $color = Arr::get($config, 'color');

        $media = Media::query()
            ->where('model_type', Mockup::class)
            ->where('model_id', $mockupId)
            ->where('collection_name', 'generated_mockups')
            ->when(
                ! empty($color),
                fn ($query) => $query->where(
                    'custom_properties->hex',
                    trim($color, '#')
                )
            )
            ->where('custom_properties->template_id', (string) $itemable->id)
            ->where('custom_properties->category_id', (int) $categoryId)
            ->first();

        if (! $media) {
            return;
        }

        $media->setCustomProperty('cart_item_id', $cartItem->id);
        $media->setCustomProperty('cart_id', $cart->id);
        $media->save();
    }

    private function isReward(BundleItem $bundleItem): bool
    {
        return $this->getBundleItemRole($bundleItem) === 'reward';
    }

    private function getBundleItemRole(BundleItem $bundleItem): string
    {
        return $bundleItem->role?->value ?? $bundleItem->role;
    }
}
