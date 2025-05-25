<?php

namespace App\Actions\Template;

use App\Services\TemplateService;

class StoreTemplate
{

    public function __construct(public TemplateService $service)
    {
    }

    public function handle($data)
    {
        return $this->service->storeResource($data);
    }
}
