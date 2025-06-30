<?php

namespace App\Http\Requests\Comment;

use App\Http\Requests\Base\BaseRequest;
use App\Models\Comment;
use App\Rules\ValidCommentable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CommentRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
        $allowedTypes = (getActiveGuard() === 'web') ? ['template'] : ['design'];
        return [
            'body' => ['required', 'string', 'min:1'],
            'commentable_type' => ['required', Rule::in($allowedTypes)],
            'commentable_id' => [
                'required',
                'string',
                new ValidCommentable('commentable_type', [
                    'design' => 'designs',
                    'template' => 'templates',
                ])
            ],
            'position_x' => ['required', 'numeric', 'between:0,10000'],
            'position_y' => ['required', 'numeric', 'between:0,10000'],
            'parent_id' => [
                'nullable', 'integer', 'exists:comments,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $parentComment = Comment::find($value);
                        if ($parentComment && $parentComment->parent_id !== null) {
                            $fail('The parent comment cannot itself be a reply.');
                        }
                    }
                },
            ],


        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Comment text is required.',
            'position_x.required' => 'You must specify the X coordinate.',
            'position_y.required' => 'You must specify the Y coordinate.',
            'owner_type.in' => 'Owner type must be User or Admin.',
        ];
    }

    public function passedValidation()
    {
        $owner = Auth::guard(getActiveGuard())->user();
        $modelClass =  commentableModelClass($this->commentable_type);
        $this->merge([
            'owner_id' => $owner->id,
            'owner_type' => get_class($owner),
            'commentable_type' => $modelClass,
            'parent_type' => "App\\Models\\Comment",
        ]);
    }

}
