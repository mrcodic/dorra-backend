<?php

namespace App\Http\Requests\User\SavedItems;

use App\Http\Requests\Base\BaseRequest;
use App\Models\Design;
use App\Models\Product;
use Illuminate\Validation\Validator;

class ToggleSaveRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'savable_type' => ['required', 'in:product,design'],
            'savable_ids' => ['required', 'array'],
            'savable_ids.*' => ['required', 'integer', 'min:1'],
        ];
    }


    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->input('savable_type');
            $ids = $this->input('savable_ids');
            $modelMap = [
                'product' => Product::class,
                'design' => Design::class,
            ];
            $model = $modelMap[$type];
            $this->collect($ids)->each(function ($id) use ($model, $modelMap,$type,$validator) {
                if (!$model::whereKey($id)->exists()) {
                    $validator->errors()->add('saveable_id', ucfirst($type) . ' not found.');
                }
            });

        });
    }
}
