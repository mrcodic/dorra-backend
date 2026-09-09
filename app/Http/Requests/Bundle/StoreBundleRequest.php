<?php

namespace App\Http\Requests\Bundle;

use Illuminate\Foundation\Http\FormRequest;

class StoreBundleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['nullable', 'string', 'max:255'],

            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string', 'max:2000'],
            'description.ar' => ['nullable', 'string', 'max:2000'],

            'status' => ['required', 'in:draft,active'],
            'repeat_type' => ['required', 'in:once,repeat'],
            'display_bundle_on_visit' => ['nullable', 'boolean'],

            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],

            'trigger' => ['required', 'array'],
            'trigger.scope' => ['required', 'in:with_category,without_category'],
            'trigger.parent_category_id' => [
                'nullable',
                'required_if:trigger.scope,with_category',
                'integer',
                'exists:categories,id',
            ],
            'trigger.item_id' => ['required', 'integer'],
            'trigger.price_id' => ['nullable', 'integer'],
            'trigger.quantity_rule' => ['nullable', 'in:any,minimum'],
            'trigger.quantity' => [
                'nullable',
                'required_if:trigger.quantity_rule,minimum',
                'integer',
                'min:1',
            ],

            'rewards' => ['required', 'array', 'min:1'],
            'rewards.*.scope' => ['required', 'in:with_category,without_category'],
            'rewards.*.parent_category_id' => [
                'nullable',
                'required_if:rewards.*.scope,with_category',
                'integer',
                'exists:categories,id',
            ],
            'rewards.*.item_id' => ['required', 'integer'],
            'rewards.*.price_id' => ['nullable', 'integer'],
            'rewards.*.quantity' => ['nullable', 'integer', 'min:1'],
            'rewards.*.discount_type' => ['required', 'in:free,percentage'],
            'rewards.*.discount_value' => [
                'nullable',
                'required_if:rewards.*.discount_type,percentage',
                'numeric',
                'gt:0',
                'max:100',
            ],
            'rewards.*.max_discount_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'trigger.parent_category_id.required_if' => 'Please choose the trigger product group first.',
            'trigger.item_id.required' => 'Please choose the trigger item.',
            'trigger.quantity.required_if' => 'Trigger quantity is required when minimum quantity is selected.',
            'rewards.required' => 'Add at least one reward.',
            'rewards.min' => 'Add at least one reward.',
            'rewards.*.parent_category_id.required_if' => 'Please choose the reward product group.',
            'rewards.*.item_id.required' => 'Please choose a reward item.',
            'rewards.*.discount_value.required_if' => 'Discount percentage is required for percentage rewards.',
        ];
    }

    public function attributes(): array
    {
        return [
            'display_bundle_on_visit' => 'display bundle on visit',
            'trigger.item_id' => 'trigger item',
            'trigger.price_id' => 'trigger quantity / price option',
            'trigger.quantity' => 'trigger quantity',
            'rewards.*.item_id' => 'reward item',
            'rewards.*.price_id' => 'reward quantity / price option',
            'rewards.*.quantity' => 'reward quantity',
            'rewards.*.discount_value' => 'reward discount',
        ];
    }
}
