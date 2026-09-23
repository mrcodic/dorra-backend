<?php

namespace App\Http\Requests\User\Favourite;

use App\Http\Requests\Base\BaseRequest;
use Illuminate\Validation\Rule;

class ToggleFavouriteRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'favouritable_type' => strtolower((string) $this->input('favouritable_type')),
            'contextable_type' => strtolower((string) $this->input('contextable_type')),
        ]);
    }

    public function rules(): array
    {
        $favouritableIdRules = ['required'];
        if ($this->input('favouritable_type') === 'template') {
            $favouritableIdRules[] = 'uuid';
            $favouritableIdRules[] = Rule::exists('templates', 'id');
        }

        $contextableIdRules = ['required'];
        if ($this->input('contextable_type') === 'product') {
            $contextableIdRules[] = 'integer';
            $contextableIdRules[] = Rule::exists('products', 'id');
        } elseif ($this->input('contextable_type') === 'category') {
            $contextableIdRules[] = 'integer';
            $contextableIdRules[] = Rule::exists('categories', 'id');
        }

        return [
            'favouritable_type' => ['required', Rule::in(['template'])],
            'favouritable_id' => $favouritableIdRules,
            'contextable_type' => ['required', Rule::in(['product', 'category'])],
            'contextable_id' => $contextableIdRules,
        ];
    }
}
