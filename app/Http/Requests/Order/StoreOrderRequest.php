<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\Base\BaseRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends BaseRequest
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
    public function rules()
    {
        return [
            // User Info
            'user_info' => 'required|array',
            'user_info.*' => 'required',

            // Pricing Details
            'pricing_details' => 'required|array',
            'pricing_details.sub_total' => 'required|numeric|min:0',
            'pricing_details.tax' => 'required|numeric|min:0',
            'pricing_details.delivery' => 'required|numeric|min:0',
            'pricing_details.discount' => 'required|numeric|min:0',
            'pricing_details.total' => 'required|numeric|min:0',

            // Product Info
            'product_id' => 'required|integer|exists:products,id',
            'product_name' => 'required|string|max:255',

            // Template Info
            'template_info' => 'required|array',
            'template_info.id' => 'required|string',
            'template_info.template_image' => 'nullable|string',

            // Personal Info
            'personal_info' => 'required|array',
            'personal_info.first_name' => 'required|string|max:100',
            'personal_info.last_name' => 'required|string|max:100',
            'personal_info.email' => 'required|email|max:255',
            'personal_info.phone_number' => 'required|string|max:20',

            // Shipping Info
            'shipping_info' => 'required|array',
            'shipping_info.id' => 'required|integer',
            'shipping_info.label' => 'required|string|max:255',
            'shipping_info.line' => 'required|string|max:255',

            // Notification Options
            'track_order' => 'sometimes|boolean',
            'send_email' => 'sometimes|boolean',
            'send_notification' => 'sometimes|boolean'
        ];
    }

}
