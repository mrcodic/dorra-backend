<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Services\TagService;
use App\Http\Requests\Tag\{StoreTagRequest, UpdateTagRequest};
use Illuminate\Http\JsonResponse;


class TagController extends DashboardController
{
    public function __construct(public TagService $tagService)
    {

        parent::__construct($tagService);
        $this->storeRequestClass = new StoreTagRequest();
        $this->updateRequestClass = new UpdateTagRequest();
        $this->indexView = 'tags.index';
        $this->createView = 'tags.create';
        $this->editView = 'tags.edit';
        $this->usePagination = true;
    }
    public function getData(): JsonResponse
    {
        return $this->tagService->getData();
    }
}
