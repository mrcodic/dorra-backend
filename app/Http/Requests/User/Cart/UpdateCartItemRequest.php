<?php

namespace App\Http\Requests\User\Cart;


use App\Http\Requests\Base\BaseRequest;


class UpdateCartItemRequest extends BaseRequest
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
            "product_price_id" => ["sometimes", "exists:product_prices,id"],
            "specs" => ["sometimes", "array"],
            "specs.*.id" => ["sometimes", "exists:product_specifications,id"],
            "specs.*.option" => ["sometimes", "exists:product_specification_options,id"],
        ];
    }


}
