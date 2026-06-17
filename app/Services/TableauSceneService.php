<?php

namespace App\Services;

use App\Repositories\Interfaces\TableauSceneRepositoryInterface;


class TableauSceneService extends BaseService
{
    public function __construct(TableauSceneRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }
    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $model = parent::storeResource($validatedData, $relationsToStore, $relationsToLoad);
        attachMediaToModel($validatedData['image_id'],$model,'tableau_scene_image');
        return $model;
    }

}
