<?php

namespace App\Http\Requests\Invitation;

use App\Enums\Invitation\StatusEnum;
use App\Models\Invitation;
use Illuminate\Foundation\Http\FormRequest;

class SendInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'emails' => ['required', 'array'],
            'emails.*' => ['required', 'email', 'exists:users,email'],
            'design_id' => ['required_without:team_id', 'nullable', 'exists:designs,id'],
            'team_id' => ['required_without:design_id', 'nullable', 'exists:teams,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('design_id') && $this->filled('team_id')) {
                $validator->errors()->add('team_id', 'You may only specify either design_id or team_id, not both.');
            }
            $existingAccepted = Invitation::where('email', $this->email)
                ->where('status', StatusEnum::ACCEPTED)
                ->when($this->design_id, fn($q) => $q->where('design_id', $this->design_id))
                ->when($this->team_id, fn($q) => $q->where('team_id', $this->team_id))
                ->exists();

            if ($existingAccepted) {
                $validator->errors()->add('email', 'This user has already accepted a previous invitation.');
            }
        });
    }
}
