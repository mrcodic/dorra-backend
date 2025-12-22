<?php

namespace App\Services;

use App\Repositories\Interfaces\FontRepositoryInterface;

class FontService extends BaseService
{

    public function __construct(FontRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $model = $this->repository->create($validatedData);
        $fontStyles = $model->fontStyles()->createMany($validatedData['font_styles']);
        return $model;

    }


}
