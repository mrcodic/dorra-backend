<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class MediaResource extends JsonResource
{
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'file_name' => $this->file_name,
            'size' =>$this->size,
            'mime_type' => $this->mime_type,
            'url' => $this->getFullUrl(),
        ];
    }

}
