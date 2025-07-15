<?php

namespace App\Http\Requests\User\Cart;

use App\Enums\HttpEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\Design;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class AddToCartRequest extends BaseRequest
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
            'design_id' => ['required', 'string', 'exists:designs,id'],
        ];
    }

    public function passedValidation()
    {
        $activeGuard = getActiveGuard();
        $userId = $activeGuard === 'sanctum' ? Auth::guard($activeGuard)->id() : null;
        $cookie = request()->cookie('cookie_id');
        $design = Design::query()->find($this->input('design_id'));
        if ($design->product->prices->isNotEmpty() && !$design->product_price_id) {
            throw new HttpResponseException(
                Response::api(
                    HttpEnum::UNPROCESSABLE_ENTITY,
                    message: 'Validation error',
                    errors: ["user_id" => 'You must select at least one price. '],
                )
            );
        }
        if (empty($userId) && empty($cookie)) {
            throw new HttpResponseException(
                Response::api(
                    HttpEnum::UNPROCESSABLE_ENTITY,
                    message: 'Validation error',
                    errors: ["user_id" => 'Either user ID or cookie ID must be present.']
                )
            );
        }
        $this->merge([
            'user_id' => $userId,
            'cookie_id' => $cookie,
        ]);
    }

}
