<?php

namespace App\Http\Requests\Mockup;

use App\Enums\Mockup\TypeEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\Product;
use Illuminate\Validation\Rule;

class UpdateMockupEditorRequest extends BaseRequest
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
            'colors' => ['required', 'array'],
            'area_top' => ['required', 'numeric', 'min:0'],
            'area_left' => ['required', 'numeric', 'min:0'],
            'area_height' => ['required', 'numeric', 'min:0'],
            'area_width' => ['required', 'numeric', 'min:0'],
        ];
    }


}
