<?php

namespace App\Http\Requests\Design;

use App\Http\Requests\Base\BaseRequest;
use App\Models\Template;
use Illuminate\Support\Str;


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

        ];

    }
    protected function passedValidation()
    {

        $template = Template::find($this->template_id);
        if (auth()->check())
        {

            $this->merge([
                'user_id' =>auth()->user()->id ,
                'design_data' => $template->value('design_data'),
                'design_url' => $template->value('preview_image'),
            ]);
        }
        else
        {
            $cookie = request()->cookie('cookie_id');

            if (!$cookie)
            {

                $cookie= (string) Str::uuid();

                cookie()->queue(cookie('cookie_id', $cookie, 60 * 24 * 30));
            }
            $this->merge([
                'cookie_id' => $cookie ,
                'design_data' => $template->value('design_data'),
                'design_url' => $template->getRawOriginal('preview_image'),
            ]);
        }



    }


}
