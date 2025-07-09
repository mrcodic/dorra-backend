<?php

namespace App\Http\Requests\Design;

use App\Enums\Template\UnitEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\{Guest, Product, Template};
use App\Rules\DimensionWithinUnitRange;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreDesignRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'template_id' => ['required_without:product_id', 'prohibits:product_id', 'exists:templates,id'],
            'product_id' => ['required_without:template_id', 'prohibits:template_id', 'exists:products,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'guest_id' => ['nullable', 'exists:guests,id'],
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
                    $this->merge(['product_id' => $product->id]);
                }
            }
        });
    }

    protected function passedValidation()
    {
        $template = Template::find($this->template_id);
        $cookieValue = getCookieId('cookie_id');
        $activeGuard = getActiveGuard();


        $userId = match ($activeGuard) {
            'web' => $this->input('user_id'),
            'sanctum' => Auth::guard('sanctum')->id(),
            default => null,
        };


        $guestId = null;
        if (!$userId && $cookieValue) {
            $guest = Guest::firstOrCreate(['cookie_value' => $cookieValue]);
            $guestId = $guest->id;
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
            'guest_id' => $guestId,
            'design_data' => $template?->design_data ?? $this->input('design_data'),
            'name' => $template?->name ?? $this->input('name'),
            'description' => $template?->description ?? $this->input('description'),
            'height' => $height,
            'width' => $width,
            'unit' => $unit,
        ]);
    }
}
