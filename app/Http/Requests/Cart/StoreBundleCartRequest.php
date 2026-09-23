<?php

namespace App\Http\Requests\Cart;

use App\Http\Requests\Base\BaseRequest;
use App\Models\Bundle;
use App\Models\BundleItem;
use App\Models\Design;
use App\Models\Template;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreBundleCartRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bundle_id' => [
                'required',
                'integer',
                'exists:bundles,id',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.bundle_item_id' => [
                'required',
                'integer',
                'exists:bundle_items,id',
            ],

            'items.*.template_id' => [
                'nullable',
                Rule::exists((new Template())->getTable(), 'id'),
            ],

            'items.*.design_id' => [
                'nullable',
                Rule::exists((new Design())->getTable(), 'id'),
            ],

            'items.*.quantity' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'items.*.specs' => [
                'nullable',
                'array',
            ],

            'items.*.specs.*.id' => [
                'required_with:items.*.specs',
                'integer',
                'exists:product_specifications,id',
            ],

            'items.*.specs.*.option' => [
                'required_with:items.*.specs',
                'integer',
                'exists:product_specification_options,id',
            ],

            'items.*.color' => [
                'nullable',
                'string',
            ],

            'items.*.mockup_id' => [
                'nullable',
                'integer',
                'exists:mockups,id',
            ],
        ];
    }

    protected function passedValidation(): void
    {
        $bundle = Bundle::query()
            ->with([
                'trigger.itemable',
                'rewards.itemable',
            ])
            ->find($this->input('bundle_id'));

        if (! $bundle) {
            return;
        }

        $bundleItems = collect([$bundle->trigger])
            ->merge($bundle->rewards)
            ->filter()
            ->values();

        $requestItems = collect($this->input('items', []))
            ->values();

        $this->validateEveryItemHasTemplateOrDesign($requestItems,$bundle);

        $requestBundleItemIds = $requestItems
            ->pluck('bundle_item_id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $uniqueRequestBundleItemIds = $requestBundleItemIds
            ->unique()
            ->values();

        $requiredBundleItemIds = $bundleItems
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $missingIds = $requiredBundleItemIds
            ->diff($uniqueRequestBundleItemIds)
            ->values();

        if ($missingIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => [
                    'Missing configuration for bundle items: ' . $missingIds->implode(', '),
                ],
            ]);
        }

        $extraIds = $uniqueRequestBundleItemIds
            ->diff($requiredBundleItemIds)
            ->values();

        if ($extraIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => [
                    'Invalid bundle items for this bundle: ' . $extraIds->implode(', '),
                ],
            ]);
        }

        foreach ($bundleItems as $bundleItem) {
            $requiredQuantity = max((int) ($bundleItem->quantity ?? 1), 1);

            $configs = $requestItems
                ->filter(fn ($item) => (int) data_get($item, 'bundle_item_id') === (int) $bundleItem->id)
                ->values();

            $this->ensureCustomPriceBundleItemHasSingleConfig(
                bundleItem: $bundleItem,
                configs: $configs
            );

            $sentQuantity = $this->resolveSentQuantityForBundleItem(
                configs: $configs,
                requiredQuantity: $requiredQuantity
            );

            if ($sentQuantity !== $requiredQuantity) {
                throw ValidationException::withMessages([
                    'items' => [
                        "Bundle item #{$bundleItem->id} requires quantity {$requiredQuantity}, but {$sentQuantity} was sent.",
                    ],
                ]);
            }
        }

        $notBelongingItems = BundleItem::query()
            ->whereIn('id', $uniqueRequestBundleItemIds)
            ->where('bundle_id', '!=', $bundle->id)
            ->exists();

        if ($notBelongingItems) {
            throw ValidationException::withMessages([
                'items' => [
                    'Some bundle items do not belong to selected bundle.',
                ],
            ]);
        }
    }

    private function validateEveryItemHasTemplateOrDesign($requestItems,$bundle): void
    {
        if ($bundle?->template_id) {
            return;
        }
        foreach ($requestItems as $index => $item) {
            $templateId = data_get($item, 'template_id');
            $designId = data_get($item, 'design_id');

            $hasTemplate = $templateId !== null && $templateId !== '';
            $hasDesign = $designId !== null && $designId !== '';

            if (! $hasTemplate && ! $hasDesign) {
                throw ValidationException::withMessages([
                    "items.{$index}.template_id" => [
                        'Template or design is required for every bundle item.',
                    ],
                ]);
            }

            if ($hasTemplate && $hasDesign) {
                throw ValidationException::withMessages([
                    "items.{$index}.template_id" => [
                        'Send either template_id or design_id, not both.',
                    ],
                ]);
            }
        }
    }

    private function ensureCustomPriceBundleItemHasSingleConfig($bundleItem, $configs): void
    {
        if (! $this->bundleItemUsesCustomPrice($bundleItem)) {
            return;
        }

        if ($configs->count() <= 1) {
            return;
        }

        throw ValidationException::withMessages([
            'items' => [
                "Bundle item #{$bundleItem->id} uses custom price quantity. Please choose one template/design only.",
            ],
        ]);
    }

    private function bundleItemUsesCustomPrice($bundleItem): bool
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

    private function resolveSentQuantityForBundleItem($configs, int $requiredQuantity): int
    {
        if ($configs->isEmpty()) {
            return 0;
        }

        $hasAnyQuantity = $configs->contains(function ($config) {
            $quantity = data_get($config, 'quantity');

            return $quantity !== null && $quantity !== '';
        });

        /*
         * Old flow:
         * one config without quantity means full required quantity.
         */
        if (! $hasAnyQuantity && $configs->count() === 1) {
            return $requiredQuantity;
        }

        /*
         * Multiple configs without quantity:
         * each row = quantity 1.
         *
         * Rows with explicit quantity use their quantity.
         */
        return $configs->sum(function ($config) {
            $quantity = data_get($config, 'quantity');

            if ($quantity === null || $quantity === '') {
                return 1;
            }

            return max((int) $quantity, 1);
        });
    }
}
