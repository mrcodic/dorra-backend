<?php

namespace App\Services;


use App\Repositories\Interfaces\DesignRepositoryInterface;


class DesignService extends BaseService
{
    public function __construct(DesignRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $design = $this->repository->query()->firstOrCreate(['template_id'=> $validatedData['template_id']],$validatedData);
        return $design->load($relationsToLoad);
    }


}
