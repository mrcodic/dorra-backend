<?php

namespace App\Http\Requests\User\Cart;

use App\Enums\HttpEnum;
use App\Enums\Item\TypeEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Design;
use App\Models\Product;
use App\Models\Template;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreCartItemRequest extends BaseRequest
{
    private Template|null $template = null;
    private Design|null $design = null;
    private Category|Product|null $product = null;

    /**
     * Determine if the v1 is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    private function cartable(): Category|Product|null
    {
        return $this->cartable_type === Product::class
            ? Product::find($this->cartable_id)
            : ($this->cartable_type === Category::class
                ? Category::find($this->cartable_id)
                : null);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $cartableType = $this->cartable_type;
        $cartable     = $this->cartable();
        $hasSpecs     = (bool) $cartable?->specifications()->exists();
        $type = (int) $this->input('type');
        return [
            'type' =>['required',Rule::in(TypeEnum::values())],
            'design_id' => ['required_without:template_id', 'string', 'exists:designs,id'],
            'template_id' => ['required_without:design_id', 'string', 'exists:templates,id'],
            'cartable_type' => [
                Rule::requiredIf($type == TypeEnum::PRINT->value),
                 'in:' . Product::class . ',' . Category::class],
            'cartable_id' => [
                Rule::requiredIf($type == TypeEnum::PRINT->value),
                'integer',
                Rule::when(
                    $cartableType === Product::class,
                    Rule::exists('products', 'id'),
                    Rule::exists('categories', 'id')
                ),
            ],
            'product_price_id' => [
                Rule::requiredIf(function () use($type){
                    $cartable = Product::find($this->cartable_id) ?? Category::find($this->cartable_id);
                    return $cartable && $cartable->prices()->exists() && $type == TypeEnum::PRINT->value;
                }),
                'exists:product_prices,id',
            ],
            'specs' => [
                Rule::requiredIf(function () use($hasSpecs,$type){
                    return $hasSpecs && $type == TypeEnum::PRINT->value;
                }),
                'array',
                $hasSpecs ? 'min:1' : 'sometimes',
            ],
            'specs.*.id' => [
                Rule::requiredIf(function () use($hasSpecs,$type){
                    return $hasSpecs && $type == TypeEnum::PRINT->value;
                }),
                'integer',
                'exists:product_specifications,id',
            ],
            'specs.*.option' => [
                Rule::requiredIf(function () use($hasSpecs,$type){
                    return $hasSpecs && $type == TypeEnum::PRINT->value;
                }),
                'integer',
                'exists:product_specification_options,id',
            ],
            'color' => ['nullable', 'string'],
        ];
    }

    public function passedValidation()
    {
        $this->template = Template::find($this->template_id);
        $this->design = Design::find($this->design_id);
        $this->product = $this->cartable_type === Product::class
            ? Product::find($this->cartable_id)
            : Category::find($this->cartable_id);

        if ($this->product?->is_has_category) {
            throw new HttpResponseException(
                Response::api(HttpEnum::UNPROCESSABLE_ENTITY, 'Validation error', [
                    'product_price_id' => 'You Cannot add product with categories.',
                ])
            );
        }
        if ($this->template && !$this->template->products->contains($this->cartable_id) && $this->cartable_type == Product::class) {
            throw new HttpResponseException(
                Response::api(HttpEnum::UNPROCESSABLE_ENTITY, 'Validation error', [
                    'cartable_id' => 'The selected category is not associated with the selected template.',
                ])
            );
        }
        if ($this->template && !$this->template->categories->contains($this->cartable_id) && $this->cartable_type == Category::class) {
            throw new HttpResponseException(
                Response::api(HttpEnum::UNPROCESSABLE_ENTITY, 'Validation error', [
                    'cartable_id' => 'The selected product is not associated with the selected template.',
                ])
            );
        }
        if ($this->design && !$this->design->designable_id && $this->cartable_type == Category::class) {
            throw new HttpResponseException(
                Response::api(HttpEnum::UNPROCESSABLE_ENTITY, 'Validation error', [
                    'cartable_id' => 'The selected product is not associated with the selected template.',
                ])
            );
        }
        if ($this->design && !$this->design->designable_id && $this->cartable_type == Product::class) {
            throw new HttpResponseException(
                Response::api(HttpEnum::UNPROCESSABLE_ENTITY, 'Validation error', [
                    'cartable_id' => 'The selected category is not associated with the selected template.',
                ])
            );
        }


        if ($this->cartable && $this->cartable->prices->isNotEmpty() && !$this->product_price_id) {
            throw new HttpResponseException(
                Response::api(HttpEnum::UNPROCESSABLE_ENTITY, 'Validation error', [
                    'product_price_id' => 'You must select at least one price.',
                ])
            );
        }

        if ($this->cartable && $this->has('specs')) {
            foreach ($this->specs as $index => $spec) {
                if (!$this->product->specifications->contains($spec['id'])) {
                    throw new HttpResponseException(
                        Response::api(HttpEnum::UNPROCESSABLE_ENTITY, 'Validation error', [
                            "specs.$index.id" => 'Invalid specification for selected product.',
                        ])
                    );
                }

                $found = $this->cartable->specifications->firstWhere('id', $spec['id'])?->options->contains($spec['option']);
                if (!$found) {
                    throw new HttpResponseException(
                        Response::api(HttpEnum::UNPROCESSABLE_ENTITY, 'Validation error', [
                            "specs.$index.option" => 'Invalid option for selected specification.',
                        ])
                    );
                }
            }
        }

        $cartId = auth('sanctum')->user()->cart->id ?? null;

        $exists = CartItem::where('cart_id', $cartId)
            ->where('cartable_id', $this->cartable_id)
            ->where('cartable_type', $this->cartable_type)
            ->when($this->product_price_id, fn($q) => $q->where('product_price_id', $this->product_price_id))
            ->when($this->template_id, fn($q) => $q->where('itemable_id', $this->template_id))
            ->when($this->design_id, fn($q) => $q->where('itemable_id', $this->design_id))
            ->exists();


        if ($exists) {
            throw ValidationException::withMessages([
                'cart_item' => 'This item is already in your cart.',
            ]);
        }


    }

    public function getTemplate(): ?Template
    {
        return $this->template;
    }

    public function getDesign(): ?Design
    {
        return $this->design;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    protected function prepareForValidation()
    {
        $map = [
            'product' => \App\Models\Product::class,
            'category' => \App\Models\Category::class,
        ];

        if (isset($map[$this->cartable_type])) {
            $this->merge([
                'cartable_type' => $map[$this->cartable_type],
            ]);
        }
    }

}
