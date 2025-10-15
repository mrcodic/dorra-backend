<?php

namespace App\Http\Requests\StationStatus;

use App\Http\Requests\Base\BaseRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Validation\Rule;

class StoreStationStatusRequest extends BaseRequest
{
    /**
     * Determine if the v1 is authorized to make this request.
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
            'name' => [
                'required',
                'string',
                'max:255',],
            'station_id' => ['required', 'integer', 'exists:stations,id'],
            'resourceable_type' => [
                'required',
                'string',
                Rule::in([Product::class, Category::class]),
            ],
            'resourceable_id'   => ['required', 'integer'],

        ];
    }


}
