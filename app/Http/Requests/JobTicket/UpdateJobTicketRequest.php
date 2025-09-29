<?php

namespace App\Http\Requests\JobTicket;

use App\Enums\JobTicket\PriorityEnum;
use App\Http\Requests\Base\BaseRequest;

class UpdateJobTicketRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
        'station_id' => 'required|exists:stations,id',
            'priority' =>['required','in:'.PriorityEnum::getValuesAsString()],
            'due_at' => 'required|date|after_or_equal:today',
        ];


    }



}
