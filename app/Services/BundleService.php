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

            $bundle = $this->repository->create($bundleData);

            $this->createTrigger($bundle, $triggerData);
            $this->createRewards($bundle, $rewardsData);

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

            $bundle->update($bundleData);

            /*
             * Soft-delete old rule rows instead of hard deleting them.
             * This keeps future cart/order references valid.
             */
            $bundle->items()->get()->each->delete();

            $this->createTrigger($bundle, $triggerData);
            $this->createRewards($bundle, $rewardsData);

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
            ->when(request()->filled('application_type'), function ($query) {
                $query->where('application_type', request('application_type'));
            })
            ->latest();

        return DataTables::of($query)
            ->addColumn('name_translate', fn (Bundle $bundle) => $bundle->getTranslations('name'))
            ->addColumn('description_translate', fn (Bundle $bundle) => $bundle->getTranslations('description'))
            ->editColumn('name', fn (Bundle $bundle) => $bundle->name)
            ->addColumn('status_data', function (Bundle $bundle) {
                return [
                    'value' => $bundle->status->value,
                    'label' => $bundle->display_status,
                ];
            })
            ->addColumn('application_type_data', function (Bundle $bundle) {
                return [
                    'value' => $bundle->application_type->value,
                    'label' => $bundle->application_type->label(),
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

        return [
            'id' => $item->id,
            'name' => $item->name,
            'scope' => $scope,
            'model_type' => $item->getMorphClass(),
            'flow' => $this->purchaseFlowResolver->resolve($item),
        ];
    }

    private function splitPayload(array $validatedData): array
    {
        $trigger = Arr::pull($validatedData, 'trigger');
        $rewards = Arr::pull($validatedData, 'rewards');

        $validatedData['auto_add_ready_rewards'] =
            (bool) ($validatedData['auto_add_ready_rewards'] ?? false);

        $validatedData['show_on_website'] =
            (bool) ($validatedData['show_on_website'] ?? false);

        $validatedData['show_on_product_page'] =
            (bool) ($validatedData['show_on_product_page'] ?? false);

        return [$validatedData, $trigger, $rewards];
    }

    private function createTrigger(Bundle $bundle, array $data): BundleItem
    {
        $item = $this->resolveSelectableItem($data, 'trigger');

        $quantityRule = $data['quantity_rule'] ?? QuantityRuleEnum::ANY->value;
        $quantity = $quantityRule === QuantityRuleEnum::ANY->value
            ? 1
            : (int) ($data['quantity'] ?? 1);

        return $bundle->items()->create([
            'itemable_type' => $item->getMorphClass(),
            'itemable_id' => $item->id,
            'role' => ItemRoleEnum::TRIGGER->value,
            'quantity_rule' => $quantityRule,
            'quantity' => $quantity,
            'discount_type' => null,
            'discount_value' => null,
            'max_discount_amount' => null,
            'sort_order' => 0,
        ]);
    }

    private function createRewards(Bundle $bundle, array $rewards): void
    {
        collect($rewards)
            ->values()
            ->each(function (array $data, int $index) use ($bundle) {
                $item = $this->resolveSelectableItem($data, "rewards.$index");

                $discountType = $data['discount_type'];

                $discountValue = $discountType === DiscountTypeEnum::FREE->value
                    ? 100
                    : (float) $data['discount_value'];

                $bundle->items()->create([
                    'itemable_type' => $item->getMorphClass(),
                    'itemable_id' => $item->id,
                    'role' => ItemRoleEnum::REWARD->value,
                    'quantity_rule' => null,
                    'quantity' => (int) $data['quantity'],
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                    'max_discount_amount' => $data['max_discount_amount'] ?? null,
                    'sort_order' => $index + 1,
                ]);
            });
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
            'discount_type' => $bundleItem->discount_type?->value,
            'discount_value' => $bundleItem->discount_value,
            'max_discount_amount' => $bundleItem->max_discount_amount,
        ];
    }
}
