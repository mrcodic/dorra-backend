<?php

namespace App\Services;


use App\Jobs\ProcessBase64Image;
use App\Jobs\RenderFabricJsonToPngJob;
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
        if (!empty($validatedData['template_id'])) {
          $design = $this->handleTransaction(function () use ($validatedData) {
                $design = $this->repository->query()->firstOrCreate(['template_id' => $validatedData['template_id']], $validatedData);
               $this->templateRepository
                   ->find($validatedData['template_id'])
                   ->getFirstMedia('templates')
                   ->copy($design, 'designs');
                return $design;
            });

        } else {
            $design = $this->repository->query()->create($validatedData);
            RenderFabricJsonToPngJob::dispatch($validatedData['design_data'], $design, 'designs');
        }
        return $design->load($relationsToLoad);
    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        $model = $this->repository->update($validatedData, $id);
        $files = request()->allFiles();
        if ($files) {
            handleMediaUploads($files, $model, clearExisting: true);
        }
        if (isset($validatedData['base64_preview_image'])) {
            ProcessBase64Image::dispatch($validatedData['base64_preview_image'], $model);
        }
        return $model->load($relationsToLoad);
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
        })->latest()->paginate();
    }

    public function getDesignVersions($designId)
    {
        return $this->repository->find($designId)->versions()->paginate();
    }


}
