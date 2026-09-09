<?php

namespace App\Services;

use App\Enums\Bundle\DiscountTypeEnum;
use App\Enums\Bundle\ItemRoleEnum;
use App\Enums\Bundle\QuantityRuleEnum;
use App\Models\Bundle;
use App\Models\BundleItem;
use App\Models\Category;
use App\Models\Product;
use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\BundleRepositoryInterface;
use App\Services\Bundle\BundleItemPurchaseFlowResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class BundleService extends BaseService
{
    public BaseRepositoryInterface $repository;

    public function __construct(
        BundleRepositoryInterface $repository,
        public BundleItemPurchaseFlowResolver $purchaseFlowResolver,
    ) {
        parent::__construct($repository);
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        return $this->handleTransaction(function () use ($validatedData, $relationsToLoad) {
            [$bundleData, $triggerData, $rewardsData] = $this->splitPayload($validatedData);

            $this->ensureOnlyOneDisplayBundleOnVisit($bundleData);

            $bundle = $this->repository->create($bundleData);

            $triggerItem = $this->createTrigger($bundle, $triggerData);

            $this->createRewards($bundle, $rewardsData, $triggerItem);

            return $bundle->load($relationsToLoad ?: [
                'trigger.itemable',
                'rewards.itemable',
            ]);
        });
    }

    public function updateResource($validatedData, $id, $relations = [])
    {
        return $this->handleTransaction(function () use ($validatedData, $id, $relations) {
            /** @var Bundle $bundle */
            $bundle = $this->repository
                ->query()
                ->with(['items'])
                ->findOrFail($id);

            [$bundleData, $triggerData, $rewardsData] = $this->splitPayload($validatedData);

            $this->ensureOnlyOneDisplayBundleOnVisit($bundleData, $bundle->id);

            $bundle->update($bundleData);

            /*
             * Soft-delete old rule rows instead of hard deleting them.
             * This keeps future cart/order references valid.
             */
            $bundle->items()->get()->each->delete();

            $triggerItem = $this->createTrigger($bundle, $triggerData);

            $this->createRewards($bundle, $rewardsData, $triggerItem);

            return $bundle->fresh($relations ?: [
                'trigger.itemable',
                'rewards.itemable',
            ]);
        });
    }

    public function getData(): JsonResponse
    {
        $locale = app()->getLocale();

        $query = $this->repository
            ->query()
            ->with([
                'trigger.itemable',
                'rewards.itemable',
            ])
            ->withCount('rewards')
            ->when(request()->filled('search_value'), function ($query) use ($locale) {
                $search = trim((string) request('search_value'));

                if ($search !== '') {
                    $query->whereRaw(
                        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?",
                        ['%' . strtolower($search) . '%']
                    );
                }
            })
            ->when(request()->filled('status'), function ($query) {
                $query->where('status', request('status'));
            })
            ->latest();

        return DataTables::of($query)
            ->addColumn('name_translate', fn (Bundle $bundle) => $bundle->getTranslations('name'))
            ->addColumn('description_translate', fn (Bundle $bundle) => $bundle->getTranslations('description'))
            ->editColumn('name', fn (Bundle $bundle) => $bundle->name)
            ->editColumn('display_bundle_on_visit', fn (Bundle $bundle) => (bool) $bundle->display_bundle_on_visit)
            ->addColumn('status_data', function (Bundle $bundle) {
                return [
                    'value' => $bundle->status->value,
                    'label' => $bundle->display_status,
                ];
            })
            ->addColumn('repeat_type_data', function (Bundle $bundle) {
                return [
                    'value' => $bundle->repeat_type->value,
                    'label' => $bundle->repeat_type->label(),
                ];
            })
            ->addColumn('trigger_data', fn (Bundle $bundle) => $this->serializeItem($bundle->trigger))
            ->addColumn('rewards_data', function (Bundle $bundle) {
                return $bundle->rewards
                    ->map(fn (BundleItem $item) => $this->serializeItem($item))
                    ->values()
                    ->all();
            })
            ->editColumn('start_at', fn (Bundle $bundle) => $bundle->start_at?->format('Y-m-d'))
            ->editColumn('end_at', fn (Bundle $bundle) => $bundle->end_at?->format('Y-m-d'))
            ->addColumn('action', function () {
                return [
                    'can_show' => (bool) auth()->user()->hasPermissionTo('bundles_show'),
                    'can_edit' => (bool) auth()->user()->hasPermissionTo('bundles_update'),
                    'can_delete' => (bool) auth()->user()->hasPermissionTo('bundles_delete'),
                ];
            })
            ->make(true);
    }

    public function itemMeta(string $scope, int $itemId, ?int $parentCategoryId = null): array
    {
        $item = $this->resolveSelectableItem(
            [
                'scope' => $scope,
                'item_id' => $itemId,
                'parent_category_id' => $parentCategoryId,
            ],
            'item'
        );

        $prices = $this->getBundleItemPriceOptions($item);

        return [
            'id' => $item->id,
            'name' => $item->name,
            'scope' => $scope,
            'model_type' => $item->getMorphClass(),

            'has_prices' => count($prices) > 0,
            'prices' => $prices,
            'price_options' => $prices,

            'flow' => $this->purchaseFlowResolver->resolve($item),
        ];
    }

    private function splitPayload(array $validatedData): array
    {
        $trigger = Arr::pull($validatedData, 'trigger');
        $rewards = Arr::pull($validatedData, 'rewards');

        $validatedData['display_bundle_on_visit'] =
            (bool) ($validatedData['display_bundle_on_visit'] ?? false);

        return [$validatedData, $trigger, $rewards];
    }

    private function ensureOnlyOneDisplayBundleOnVisit(array $bundleData, ?int $exceptBundleId = null): void
    {
        if (empty($bundleData['display_bundle_on_visit'])) {
            return;
        }

        $alreadyExists = Bundle::query()
            ->where('display_bundle_on_visit', true)
            ->when($exceptBundleId, function ($query) use ($exceptBundleId) {
                $query->whereKeyNot($exceptBundleId);
            })
            ->exists();

        if ($alreadyExists) {
            throw ValidationException::withMessages([
                'display_bundle_on_visit' => 'Only one bundle can be displayed on website visit.',
            ]);
        }
    }

    private function createTrigger(Bundle $bundle, array $data): BundleItem
    {
        $item = $this->resolveSelectableItem($data, 'trigger');

        [$priceId, $priceQuantity] = $this->resolveSelectedPriceOption(
            $item,
            $data,
            'trigger.price_id'
        );

        if ($priceId) {
            $quantityRule = QuantityRuleEnum::MINIMUM->value;
            $quantity = $priceQuantity;
        } else {
            $quantityRule = $data['quantity_rule'] ?? QuantityRuleEnum::ANY->value;

            $quantity = $quantityRule === QuantityRuleEnum::ANY->value
                ? 1
                : (int) ($data['quantity'] ?? 1);
        }

        return $bundle->items()->create([
            'itemable_type' => $item->getMorphClass(),
            'itemable_id' => $item->id,
            'role' => ItemRoleEnum::TRIGGER->value,
            'quantity_rule' => $quantityRule,
            'quantity' => $quantity,
            'price_id' => $priceId,
            'discount_type' => null,
            'discount_value' => null,
            'max_discount_amount' => null,
            'sort_order' => 0,
        ]);
    }

    private function createRewards(Bundle $bundle, array $rewards, BundleItem $triggerItem): void
    {
        collect($rewards)
            ->values()
            ->each(function (array $data, int $index) use ($bundle, $triggerItem) {
                $item = $this->resolveSelectableItem($data, "rewards.$index");

                $this->ensureRewardIsDifferentFromTrigger(
                    $triggerItem,
                    $item,
                    $index
                );

                [$priceId, $priceQuantity] = $this->resolveSelectedPriceOption(
                    $item,
                    $data,
                    "rewards.$index.price_id"
                );

                $quantity = $priceId
                    ? $priceQuantity
                    : (int) ($data['quantity'] ?? 1);

                $discountType = $data['discount_type'];

                $discountValue = $discountType === DiscountTypeEnum::FREE->value
                    ? 100
                    : (float) $data['discount_value'];

                $bundle->items()->create([
                    'itemable_type' => $item->getMorphClass(),
                    'itemable_id' => $item->id,
                    'role' => ItemRoleEnum::REWARD->value,
                    'quantity_rule' => null,
                    'quantity' => $quantity,
                    'price_id' => $priceId,
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                    'max_discount_amount' => $data['max_discount_amount'] ?? null,
                    'sort_order' => $index + 1,
                ]);
            });
    }

    private function ensureRewardIsDifferentFromTrigger(
        BundleItem $triggerItem,
        Model $rewardItem,
        int $index
    ): void {
        if (
            $triggerItem->itemable_type === $rewardItem->getMorphClass()
            && (int) $triggerItem->itemable_id === (int) $rewardItem->id
        ) {
            throw ValidationException::withMessages([
                "rewards.$index.item_id" => 'Reward item cannot be the same as trigger item.',
            ]);
        }
    }

    private function resolveSelectableItem(array $data, string $attribute): Model
    {
        $scope = $data['scope'] ?? null;
        $itemId = (int) ($data['item_id'] ?? 0);
        $parentCategoryId = isset($data['parent_category_id'])
            ? (int) $data['parent_category_id']
            : null;

        if ($scope === 'with_category') {
            $product = Product::query()
                ->whereKey($itemId)
                ->whereHas('category', function ($query) use ($parentCategoryId) {
                    $query
                        ->whereKey($parentCategoryId)
                        ->where('is_has_category', 1)
                        ->where('is_tableau', 0);
                })
                ->first();

            if (! $product) {
                throw ValidationException::withMessages([
                    "$attribute.item_id" => 'The selected product does not belong to the selected product group.',
                ]);
            }

            $this->ensurePurchasable($product, $attribute);

            return $product;
        }

        if ($scope === 'without_category') {
            $category = Category::query()
                ->whereKey($itemId)
                ->where('is_has_category', 0)
                ->where('is_tableau', 0)
                ->first();

            if (! $category) {
                throw ValidationException::withMessages([
                    "$attribute.item_id" => 'The selected product is invalid.',
                ]);
            }

            $this->ensurePurchasable($category, $attribute);

            return $category;
        }

        throw ValidationException::withMessages([
            "$attribute.scope" => 'Invalid product type.',
        ]);
    }

    private function ensurePurchasable(Model $item, string $attribute): void
    {
        $flow = $this->purchaseFlowResolver->resolve($item);

        if (! $flow['can_purchase']) {
            throw ValidationException::withMessages([
                "$attribute.item_id" =>
                    'This item cannot be used in a bundle because both Add To Cart and Customize Design are disabled.',
            ]);
        }
    }

    private function resolveSelectedPriceOption(Model $item, array $data, string $attribute): array
    {
        if (! method_exists($item, 'prices')) {
            return [null, null];
        }

        $priceOptions = collect($this->getBundleItemPriceOptions($item));

        if ($priceOptions->isEmpty()) {
            return [null, null];
        }

        $priceId = isset($data['price_id'])
            ? (int) $data['price_id']
            : null;

        if (! $priceId) {
            throw ValidationException::withMessages([
                $attribute => 'Please select a quantity / price option.',
            ]);
        }

        $selectedPriceOption = $priceOptions
            ->first(fn (array $price) => (int) $price['id'] === $priceId);

        if (! $selectedPriceOption) {
            throw ValidationException::withMessages([
                $attribute => 'The selected quantity / price option is invalid.',
            ]);
        }

        $quantity = (int) ($selectedPriceOption['quantity'] ?? 0);

        if ($quantity < 1) {
            throw ValidationException::withMessages([
                $attribute => 'The selected price option does not have a valid quantity.',
            ]);
        }

        return [$priceId, $quantity];
    }

    private function serializeItem(?BundleItem $bundleItem): ?array
    {
        if (! $bundleItem || ! $bundleItem->itemable) {
            return null;
        }

        $item = $bundleItem->itemable;
        $isProduct = $item instanceof Product;

        return [
            'id' => $bundleItem->id,
            'role' => $bundleItem->role->value,
            'scope' => $isProduct ? 'with_category' : 'without_category',
            'parent_category_id' => $isProduct ? $item->category_id : null,
            'item_id' => $item->id,
            'item_name' => $item->name,
            'itemable_type' => $item->getMorphClass(),

            'quantity_rule' => $bundleItem->quantity_rule?->value,
            'quantity' => $bundleItem->quantity,

            'price_id' => $bundleItem->price_id,
            'price_label' => $this->getBundleItemPriceLabel($bundleItem),

            'discount_type' => $bundleItem->discount_type?->value,
            'discount_value' => $bundleItem->discount_value,
            'max_discount_amount' => $bundleItem->max_discount_amount,
        ];
    }

    private function getBundleItemPriceOptions($item): array
    {
        if (! method_exists($item, 'prices')) {
            return [];
        }

        $prices = $item->relationLoaded('prices')
            ? $item->prices
            : $item->prices()->get();

        return $prices
            ->map(function ($price) {
                return [
                    'id' => $price->id,
                    'quantity' => $this->getPriceQuantity($price),
                    'price' => $this->getPriceAmount($price),
                    'label' => $this->formatPriceOptionLabel($price),
                ];
            })
            ->filter(function (array $price) {
                return ! empty($price['id'])
                    && ! empty($price['quantity'])
                    && (int) $price['quantity'] > 0;
            })
            ->values()
            ->toArray();
    }

    private function getBundleItemPriceLabel(BundleItem $bundleItem): ?string
    {
        if (! $bundleItem->price_id || ! $bundleItem->itemable) {
            return null;
        }

        if (! method_exists($bundleItem->itemable, 'prices')) {
            return null;
        }

        $price = $bundleItem->itemable
            ->prices()
            ->whereKey($bundleItem->price_id)
            ->first();

        if (! $price) {
            return null;
        }

        return $this->formatPriceOptionLabel($price);
    }

    private function getPriceQuantity($price): mixed
    {
        return data_get($price, 'quantity')
            ?? data_get($price, 'qty')
            ?? data_get($price, 'min_quantity')
            ?? data_get($price, 'pieces')
            ?? data_get($price, 'count');
    }

    private function getPriceAmount($price): mixed
    {
        return data_get($price, 'price')
            ?? data_get($price, 'amount')
            ?? data_get($price, 'final_price')
            ?? data_get($price, 'base_price');
    }

    private function formatPriceOptionLabel($price): string
    {
        $quantity = $this->getPriceQuantity($price);
        $amount = $this->getPriceAmount($price);

        $label = $quantity
            ? $quantity . ' pcs'
            : 'Price Option #' . $price->id;

        if ($amount !== null) {
            $label .= ' - ' . $amount;
        }

        return $label;
    }

    public function getEntryPopupBundle()
    {
        return $this->repository
            ->query()
            ->with([
                'trigger.itemable',
                'rewards.itemable',
            ])
            ->where('display_bundle_on_visit', true)
            ->where('status', 'active')
            ->whereHas('trigger')
            ->whereHas('rewards')
            ->where(function ($query) {
                $query
                    ->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function ($query) {
                $query
                    ->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            })
            ->latest()
            ->first();
    }
}
