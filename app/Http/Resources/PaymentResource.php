<?php

namespace App\Http\Resources;

use App\Http\Resources\Design\DesignResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "code" => $this->code,
            "active" => $this->active,
            "gateway" => [
                "id" => $this->paymentGateway->id,
                "name" => $this->paymentGateway->name,
                "code" => $this->paymentGateway->code,
            ]

        ];
    }
}

