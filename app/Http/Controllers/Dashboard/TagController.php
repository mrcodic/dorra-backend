<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Services\TagService;
use App\Http\Requests\Tag\{StoreTagRequest, UpdateFlagRequest};
use Illuminate\Http\JsonResponse;


class TagController extends DashboardController
{
    public function __construct(public TagService $tagService)
    {

        parent::__construct($tagService);
        $this->storeRequestClass = new StoreTagRequest();
        $this->updateRequestClass = new UpdateFlagRequest();
        $this->indexView = 'tags.index';
        $this->usePagination = true;
        $this->resourceTable = 'tags';

    }
    public function getData(): JsonResponse
    {
        return $this->tagService->getData();
    }
}
