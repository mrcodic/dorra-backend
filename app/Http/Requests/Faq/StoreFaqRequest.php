<?php

namespace App\Http\Requests\Faq;


use App\Http\Requests\Base\BaseRequest;


class StoreFaqRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return
            [
                'question.en' => [
                    'required',
                    'string',
                    'max:255',

                ], 'question.ar' => [
                'nullable',
                'string',
                'max:255',

            ],
                'answer.en' => [
                    'required',
                    'string',
                    'max:255',

                ],
                'answer.ar' => [
                    'nullable',
                    'string',
                    'max:255',

                ],

            ];


    }



}
