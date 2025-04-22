<?php

namespace App\Http\Requests\User\SavedItems;

use App\Http\Requests\Base\BaseRequest;
use App\Models\Product;
use Illuminate\Validation\Validator;

class DeleteSaveRequest extends BaseRequest
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
            'savable_type' => ['required', 'string', 'in:project,product'],
            'savable_ids' => ['required', 'array', 'min:1'],
            'savable_ids.*' => ['required', 'integer'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->input('savable_type');
            $ids = $this->input('savable_ids');
            $modelMap = [
                'product' => Product::class,
//                'project' => Project::class,
            ];
            $model = $modelMap[$type];
           if (!$model::whereIn($ids)->exists()) {
               $validator->errors()->add('saveable_ids', ucfirst($type) . ' not found.');
           }
        });
    }
}
