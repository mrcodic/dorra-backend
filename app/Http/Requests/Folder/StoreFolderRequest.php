<?php

namespace App\Http\Requests\Folder;

use App\Enums\DiscountCode\ScopeEnum;
use App\Enums\DiscountCode\TypeEnum;
use App\Http\Requests\Base\BaseRequest;
use App\Models\Design;

class StoreFolderRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'designs' => ['nullable', 'array'],
            'designs.*' => [
                'nullable',
                'string',
                'exists:designs,id',
//                function ($attribute, $value, $fail) {
//                    $design = Design::with('users')->find($value);
//
////                    if (
////                        !$design ||
////                        !$design->users->contains('id', auth('sanctum')->id())
////                    ) {
////                        $fail("The selected design does not belong to you or you are not a member of this design.");
////                    }
//                },
                function ($attribute, $value, $fail) {
                    $design = Design::find($value);
                    if ($design && $design->folders()->pluck('id')->contains($this->folder_id)) {
                        $fail("The selected design already added to that folder.");
                    }
                }
            ],
        ];


    }

    protected function passedValidation()
    {
        $this->merge([
           'user_id' => auth('sanctum')->id(),
        ]);
    }


}
