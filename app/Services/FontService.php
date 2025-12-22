<?php

namespace App\Services;

use App\Repositories\Interfaces\FontRepositoryInterface;

class FontService extends BaseService
{

    public function __construct(FontRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }


}
