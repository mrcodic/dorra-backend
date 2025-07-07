<?php

namespace App\Http\Requests\Design;

use App\Enums\Template\UnitEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\Product;
use App\Models\Template;
use App\Rules\DimensionWithinUnitRange;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;


class StoreDesignRequest extends BaseRequest
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
            'template_id' => ['required_without:product_id', 'prohibits:product_id', 'exists:templates,id'],
            'product_id' => ['required_without:template_id', 'prohibits:template_id', 'exists:products,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'design_data' => ['nullable', 'json'],
            'product_type' => ['required_with:product_id', 'in:T-shirt,other'],
            'unit' => [
                Rule::requiredIf($this->input('product_type') === 'other'),
                'integer',
                'in:' . UnitEnum::getValuesAsString(),
            ],
            'height' => [
                Rule::requiredIf($this->input('product_type') === 'other'),
                'numeric',
                new DimensionWithinUnitRange()
            ],
            'width' => [
                Rule::requiredIf($this->input('product_type') === 'other'),
                'numeric',
                new DimensionWithinUnitRange()
            ],

            'name' => ['required_with:product_id', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];

    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $templateId = $this->input('template_id');

            if ($templateId) {
                $template = Template::find($templateId);
                if (!$template || $template->media->isEmpty()) {
                    $validator->errors()->add('template_id', 'The selected template does not have any media attached.');
                }
            }

            if ($this->input('product_type') === 'T-shirt') {
                $product = Product::where('name->en', 'T-shirt')->first();
                if (!$product) {
                    $validator->errors()->add('product_id', 'No product named T-shirt exists.');
                } else {
                    $this->merge([
                        'product_id' => $product->id
                    ]);
                }
            }
        });
    }


    protected function passedValidation()
    {
        $template = Template::find($this->template_id);
        $cookie = getCookieId('cookie_id');
        $activeGuard = getActiveGuard();

        if ($activeGuard === 'web') {
            $userId = $this->input('user_id');
        } elseif ($activeGuard === 'sanctum') {
            $userId = Auth::guard($activeGuard)->id();
        } else {
            $userId = null;
        }

        $width = $template?->width ?? $this->input('width');
        $height = $template?->height ?? $this->input('height');
        $unit = $template?->unit->value ?? $this->input('unit');


        if ($this->input('product_type') === 'T-shirt') {
            $width = 650;
            $height = 650;
            $unit = UnitEnum::PIXEL->value;
        }
        $this->merge([
            'user_id' => $userId,
            'cookie_id' => $cookie,
            'design_data' => $template?->design_data ?? $this->input('design_data'),
            'name' => $template?->name ?? $this->input('name'),
            'description' => $template?->description ?? $this->input('description'),
            'height' => $height,
            'width' => $width,
            'unit' => $unit,

        ]);
    }
}
