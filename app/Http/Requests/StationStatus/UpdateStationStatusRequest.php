<?php

namespace App\Http\Requests\StationStatus;


use App\Http\Requests\Base\BaseRequest;
use App\Models\CountryCode;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Propaganistas\LaravelPhone\Rules\Phone;

class UpdateStationStatusRequest extends BaseRequest
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
    public function rules($id): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',],
            'station_id' => ['required', 'integer', 'exists:stations,id'],
            'parent_id' => ['required', 'integer', 'exists:station_statuses,id'],
            'job_ticket_id' => ['required', 'integer', 'exists:job_tickets,id'],
        ];
    }


}
