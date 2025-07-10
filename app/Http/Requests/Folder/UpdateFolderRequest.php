<?php

namespace App\Http\Requests\Folder;

use App\Http\Requests\Base\BaseRequest;
use App\Models\Design;

class UpdateFolderRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable','string','max:1000'],
            'designs' => ['nullable', 'array'],
            'designs.*' => ['nullable', 'string', 'exists:designs,id',function ($attribute, $value, $fail) {
             $design = Design::find($value);
             if ($design && !$design->users()->pluck('id')->contains(auth('sanctum')->id())) {
                 $fail("The selected design does not belong to you or you are not a member of this design.");
             }
            }],
        ];
    }

    protected function passedValidation()
    {
        $this->merge([
           'user_id' => auth('sanctum')->id(),
        ]);
    }


}
