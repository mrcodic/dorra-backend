<?php

namespace App\Services;

use App\Repositories\Interfaces\TemplateRepositoryInterface;

class TemplateService extends BaseService
{

    public function __construct(TemplateRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }

}
