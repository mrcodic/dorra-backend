<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Resources\TableauSceneResource;
use App\Services\TableauSceneService;
use App\Http\Requests\TableauScene\{StoreTableauSceneRequest};



class TableauSceneController extends DashboardController
{
    public function __construct(public TableauSceneService $sceneService)
    {
        parent::__construct($sceneService);
        $this->storeRequestClass = new StoreTableauSceneRequest();
        $this->resourceTable = 'tableau_scenes';
        $this->resourceClass = TableauSceneResource::class;

    }
}
