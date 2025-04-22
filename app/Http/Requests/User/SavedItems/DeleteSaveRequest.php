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
                // 'project' => \App\Models\Project::class,
            ];

            if (!isset($modelMap[$type])) {
                return;
            }
            $model = $modelMap[$type];
            $existingIds = $model::whereIn('id', $ids)->pluck('id')->all();
            $missingIds = array_diff($ids, $existingIds);

            if (!empty($missingIds)) {
                $validator->errors()->add(
                    'savable_ids',
                    ucfirst($type) . ' ID(s) not found: ' . implode(', ', $missingIds)
                );
            }
        });

    }
}
