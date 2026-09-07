<?php

namespace App\Http\Requests\Bundle;

class UpdateBundleRequest extends StoreBundleRequest
{
    public function rules($id = null): array
    {
        return parent::rules();
    }
}
