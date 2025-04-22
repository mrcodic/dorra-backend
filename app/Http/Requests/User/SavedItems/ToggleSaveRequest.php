<?php

namespace App\Http\Requests\User\SavedItems;

use App\Http\Requests\Base\BaseRequest;
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
            'savable_type' => ['required', 'string','in:product,project'],
            'savable_id' => ['required', 'integer'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->input('savable_type');
            $id = $this->input('savable_id');
            $modelMap = [
                'product' => Product::class,
//                'project' => Project::class,
            ];
            $model = $modelMap[$type];
           if (!$model::whereKey($id)->exists()) {
               $validator->errors()->add('saveable_id', ucfirst($type) . ' not found.');
           }
        });
    }
}
