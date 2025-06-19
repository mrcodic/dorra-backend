<?php

namespace App\Http\Requests;

use App\Enums\HttpEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\Design;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        $design = Design::find($this->input('design_id'));
        $cookie = request()->cookie('cookie_id');

        if (empty($userId) && empty($cookie)) {
            throw new HttpResponseException(
                Response::api(
                    HttpEnum::UNPROCESSABLE_ENTITY,
                    message: 'Validation error',
                    errors: ["user_id" => 'Either user ID or cookie ID must be present.']
                )
            );
        }

       $totalPriceDesigns = Design::query()->with(['productPrice','product'])
            ->where(function ($query) use ($cookie, $userId) {
                $query->where('cookie_id', $cookie)
                    ->orWhere('user_id', $userId);
            })
            ->get()->sum('total_price');

        $this->merge([
            'price' => $totalPriceDesigns + $design->total_price,
            'user_id' => $userId,
            'cookie_id' => $cookie,
        ]);
    }

}
