<?php

namespace App\Services;


use App\Repositories\Interfaces\DesignRepositoryInterface;
use App\Repositories\Interfaces\TemplateRepositoryInterface;


class DesignService extends BaseService
{
    public function __construct(DesignRepositoryInterface $repository, public TemplateRepositoryInterface $templateRepository)
    {
        parent::__construct($repository);
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $design = $this->repository->query()->firstOrCreate(['template_id' => $validatedData['template_id']], $validatedData);
        $this->templateRepository
            ->find($validatedData['template_id'])
            ->getFirstMedia('templates')
            ->copy($design, 'designs');

        return $design->load($relationsToLoad);
    }

    public function getDesigns()
    {
        $cookieId = request()->cookie('cookie_id');
        $userId = auth('sanctum')->id();
        return $this->repository->query()->where(function ($q) use ($cookieId, $userId) {
            if ($userId) {
                $q->whereUserId($userId);
            } elseif ($cookieId) {
                $q->whereCookieId($cookieId);
            } else {

                $q->whereRaw('1 = 0');
            }
        })->paginate();
    }


}
