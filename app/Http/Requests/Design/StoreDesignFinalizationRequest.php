<?php

namespace App\Http\Requests\Design;

use App\Http\Requests\Base\BaseRequest;
use App\Models\Design;
use App\Models\Template;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;


class StoreDesignFinalizationRequest extends BaseRequest
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
        $design = Design::findOrFail($this->input('design_id'));
        return [
            "design_id" => ["required", "exists:designs,id"],
            "product_price_id" => ["sometimes", "exists:product_prices,id",function ($attribute, $value, $fail) use ($design) {
             if (!$design->product->prices->contains($value)) {
                $fail("The selected price is not valid for the chosen product.");
            }
            }],
            "quantity" => ["required_without:price_id", "integer", "min:1"],
            "specs" => ["required", "array"],
            "specs.*.id" => ["required", "exists:product_specification_template,product_specification_id",function ($attribute, $value, $fail) use ($design) {
            if (!$design->template->specifications->contains($value)) {
                $fail("The selected specification is not valid for the chosen template.");
            }
            }],
            "specs.*.options" => ["required", "array"],
            "specs.*.options.*" => ["required", "exists:product_specification_options,id",function ($attribute, $value, $fail) use ($design) {
            if (!$design->template->specifications->each(function ($spec) use ($value) {
                $spec->options->contains($value);
            })) {
                $fail("The selected option is not valid for the chosen template.");
            }
            }],

        ];

    }

}
