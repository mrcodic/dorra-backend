<?php

namespace App\Http\Requests\Design;

use App\Http\Requests\Base\BaseRequest;
use App\Models\Design;
use App\Models\Template;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;


class StoreDesignFinalizationRequest extends BaseRequest
{
    protected $design;
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
        $design = $this->design = Design::findOrFail($this->input('design_id'));
        return [
            "design_id" => ["required", "exists:designs,id"],
            "product_price_id" => [Rule::requiredIf($design->product->prices->isNotEmpty()), "exists:product_prices,id",function ($attribute, $value, $fail) use ($design) {
             if (!$design->product->prices->contains($value)) {
                $fail("The selected price is not valid for the chosen product.");
            }
            }],
            "specs" => ["required", "array"],
            "specs.*.id" => ["required", "exists:product_specification_template,product_specification_id",function ($attribute, $value, $fail) use ($design) {
            if (!$design->template->specifications->contains($value)) {
                $fail("The selected specification is not valid for the chosen template.");
            }
            }],
            "specs.*.option" => ["required", "exists:product_specification_options,id",function ($attribute, $value, $fail) use ($design) {
            if (!$design->template->specifications->each(function ($spec) use ($value) {
                $spec->load('options');
                $spec->options->contains($value);
            })) {
                $fail("The selected option is not valid for the chosen template.");
            }
            }],

        ];

    }


}
