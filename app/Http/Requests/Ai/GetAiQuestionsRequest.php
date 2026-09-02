<?php

namespace App\Http\Requests\Ai;

use Illuminate\Foundation\Http\FormRequest;

class GetAiQuestionsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ai_category_id' => [
                'required',
                'integer',
                'exists:ai_categories,id',
            ],
            'ai_studio_item_id' => [
                'required',
                'integer',
                'exists:ai_studio_items,id',
            ],
        ];
    }
}
