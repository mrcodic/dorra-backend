<?php

namespace App\Services;

use App\Repositories\Interfaces\{FolderRepositoryInterface};


class FolderService extends BaseService
{
    public function __construct(FolderRepositoryInterface $repository,)
    {
        parent::__construct($repository);
    }


}
