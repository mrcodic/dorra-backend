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

//            'applications' => [
//                'nullable',
//                'integer',
//                'min:1',
//            ],

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
                'required_without:items.*.design_id',
                'nullable',
                'integer',
                Rule::exists((new Template())->getTable(), 'id'),
            ],

            'items.*.design_id' => [
                'required_without:items.*.template_id',
                'nullable',
                'integer',
                Rule::exists((new Design())->getTable(), 'id'),
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
            ->with(['trigger', 'rewards'])
            ->find($this->input('bundle_id'));

        if (! $bundle) {
            return;
        }

        $requiredBundleItemIds = collect([$bundle->trigger])
            ->merge($bundle->rewards)
            ->filter()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $requestBundleItemIds = collect($this->input('items', []))
            ->pluck('bundle_item_id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $missingIds = $requiredBundleItemIds
            ->diff($requestBundleItemIds)
            ->values();

        if ($missingIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => [
                    'Missing configuration for bundle items: ' . $missingIds->implode(', '),
                ],
            ]);
        }

        $extraIds = $requestBundleItemIds
            ->diff($requiredBundleItemIds)
            ->values();

        if ($extraIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => [
                    'Invalid bundle items for this bundle: ' . $extraIds->implode(', '),
                ],
            ]);
        }

        $duplicatedIds = $requestBundleItemIds
            ->duplicates()
            ->values();

        if ($duplicatedIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => [
                    'Duplicated bundle items: ' . $duplicatedIds->implode(', '),
                ],
            ]);
        }

        $notBelongingItems = BundleItem::query()
            ->whereIn('id', $requestBundleItemIds)
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
}
