<?php

namespace App\Http\Requests\Design;

use App\Http\Requests\Base\BaseRequest;
use App\Models\Template;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;


class StoreDesignRequest extends BaseRequest
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
            'template_id' => ['required', 'exists:templates,id'],
            'user_id' => ['required', 'exists:users,id'],
        ];

    }
    protected function passedValidation()
    {
        $template = Template::find($this->template_id);
        $cookie = request()->cookie('cookie_id');
        $activeGuard = getActiveGuard();

        if ($activeGuard === 'web') {
            $userId = $this->input('user_id');
        } elseif ($activeGuard === 'sanctum') {
            $userId = Auth::guard($activeGuard)->id();
        } else {
            $userId = null;
        }

        if (!$cookie) {
            $cookie = (string) Str::uuid();
            cookie()->queue(cookie('cookie_id', $cookie, 60 * 24 * 30));
        }

        $this->merge([
            'user_id' => $userId,
            'cookie_id' => $cookie,
            'design_data' => $template?->design_data,
        ]);
    }
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $templateId = $this->input('template_id');

            if ($templateId) {
                $template = Template::find($templateId);
                if (!$template || $template->media->isEmpty()) {
                    $validator->errors()->add('template_id', 'The selected template does not have any media attached.');
                }
            }
        });
    }
}
