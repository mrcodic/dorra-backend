<?php

namespace App\Http\Controllers\Api\V1\User\Folder;

use App\Http\Controllers\Controller;

use App\Http\Requests\Folder\StoreFolderRequest;
use App\Services\FolderService;



class FolderController extends Controller
{
    public function __construct(public FolderService $folderService)
    {
    }

   public function store(StoreFolderRequest $request)
   {
      $folder = $this->folderService->storeResource($request->validated());
   }

}
