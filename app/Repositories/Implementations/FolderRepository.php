<?php

namespace App\Repositories\Implementations;

use App\Models\Folder;
use App\Repositories\{Base\BaseRepository, Interfaces\FolderRepositoryInterface};


class FolderRepository extends BaseRepository implements FolderRepositoryInterface
{
    public function __construct(Folder $folder)
    {
        parent::__construct($folder);
    }



}
