<?php

namespace App\Repositories\Implementations;


use App\Models\TableauScene;
use App\Repositories\{Base\BaseRepository, Interfaces\TableauSceneRepositoryInterface};


class TableauSceneRepository extends BaseRepository implements TableauSceneRepositoryInterface
{
    public function __construct(TableauScene $scene)
    {
        parent::__construct($scene);
    }



}
