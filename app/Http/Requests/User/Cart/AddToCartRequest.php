<?php

namespace App\Http\Requests\User\Cart;

use App\Enums\HttpEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\Design;
use App\Models\Product;
use App\Models\Template;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;

class AddToCartRequest extends BaseRequest
{
    private Template|null $template = null;
    private Design|null $design = null;
    private Product|null $product = null;
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
            'design_id' => ['required_without:template_id', 'string', 'exists:designs,id'],
            'template_id' => ['required_without:design_id', 'string', 'exists:templates,id'],
            'product_id' => ['required', 'exists:products,id'],
            "product_price_id" => ["nullable", "exists:product_prices,id"],
            "specs" => ["sometimes", "array"],
            "specs.*.id" => ["sometimes", "exists:product_specifications,id"],
            "specs.*.option" => ["sometimes", "exists:product_specification_options,id"],
        ];
    }

    public function passedValidation()
    {
        $this->template = Template::find($this->template_id);
        $this->design = Design::find($this->design_id);
        $this->product = Product::find($this->product_id);

        if ($this->template && !$this->template->products->contains($this->product_id)) {
            throw new HttpResponseException(
                Response::api(HttpEnum::UNPROCESSABLE_ENTITY, 'Validation error', [
                    'product_id' => 'The selected product is not associated with the selected template.',
                ])
            );
        }

        if ($this->design && $this->design->product_id !== $this->product_id) {
            throw new HttpResponseException(
                Response::api(HttpEnum::UNPROCESSABLE_ENTITY, 'Validation error', [
                    'product_id' => 'The selected product is not associated with the selected design.',
                ])
            );
        }

        if ($this->product && $this->product->prices->isNotEmpty() && !$this->product_price_id) {
            throw new HttpResponseException(
                Response::api(HttpEnum::UNPROCESSABLE_ENTITY, 'Validation error', [
                    'product_price_id' => 'You must select at least one price.',
                ])
            );
        }

        if ($this->product && $this->has('specs')) {
            foreach ($this->specs as $index => $spec) {
                if (!$this->product->specifications->contains($spec['id'])) {
                    throw new HttpResponseException(
                        Response::api(HttpEnum::UNPROCESSABLE_ENTITY, 'Validation error', [
                            "specs.$index.id" => 'Invalid specification for selected product.',
                        ])
                    );
                }

                $found = $this->product->specifications->firstWhere('id', $spec['id'])?->options->contains($spec['option']);
                if (!$found) {
                    throw new HttpResponseException(
                        Response::api(HttpEnum::UNPROCESSABLE_ENTITY, 'Validation error', [
                            "specs.$index.option" => 'Invalid option for selected specification.',
                        ])
                    );
                }
            }
        }

        $activeGuard = getActiveGuard();
        $userId = $activeGuard === 'sanctum' ? Auth::guard($activeGuard)->id() : null;
        $cookie = request()->cookie('cookie_id');

        if (empty($userId) && empty($cookie)) {
            throw new HttpResponseException(
                Response::api(HttpEnum::UNPROCESSABLE_ENTITY, 'Validation error', [
                    'user_id' => 'Either user ID or cookie ID must be present.',
                ])
            );
        }

        $this->merge([
            'user_id' => $userId,
            'cookie_id' => $cookie,
        ]);
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

}
