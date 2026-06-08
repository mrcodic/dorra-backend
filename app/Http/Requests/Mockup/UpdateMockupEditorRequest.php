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
            'positions'           => ['required', 'array'],
            'positions.*'         => ['required', 'array'],
            'positions.*.p1x'     => ['required', 'numeric'],
            'positions.*.p1y'     => ['required', 'numeric'],
            'positions.*.p2x'     => ['required', 'numeric'],
            'positions.*.p2y'     => ['required', 'numeric'],
            'positions.*.p3x'     => ['required', 'numeric'],
            'positions.*.p3y'     => ['required', 'numeric'],
            'positions.*.p4x'     => ['required', 'numeric'],
            'positions.*.p4y'     => ['required', 'numeric'],
        ];
    }


}
