<?php

namespace App\Http\Controllers\Api\V1\User\Design;



use App\Http\Controllers\Controller;
use App\Http\Requests\Design\StoreDesignRequest;
use App\Http\Requests\Design\UpdateDesignRequest;
use App\Http\Resources\DesignResource;

use App\Http\Resources\DesignVersionResource;
use App\Models\Design;
use App\Models\DesignVersion;
use App\Services\DesignService;
use Illuminate\Support\Facades\Response;


class DesignController extends Controller
{
    public function __construct(public DesignService $designService){}

    public function index()
    {
        $designs = $this->designService->getDesigns();
        return Response::api(data: DesignResource::collection($designs));

    }
    public function store(StoreDesignRequest $request)
    {
        $design = $this->designService->storeResource($request->only([
            'template_id',
            'user_id',
            'cookie_id',
            'design_data',
            'design_image',
            'current_version',
        ]));
        return Response::api(data: DesignResource::make($design->refresh()));
    }

    public function show($design)
    {
        $design = $this->designService->showResource($design,['media']);
        return Response::api(data: DesignResource::make($design->refresh()));
    }

    public function update(UpdateDesignRequest $request, $design)
    {
        $design = $this->designService->updateResource($request->validated(), $design);
        return Response::api(data: DesignResource::make($design->refresh()));
    }

    public function getDesignVersions($designId)
    {
        $designVersions = $this->designService->getDesignVersions($designId);
        return Response::api(data: DesignVersionResource::collection($designVersions));


    }
}
