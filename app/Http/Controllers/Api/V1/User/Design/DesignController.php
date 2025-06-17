<?php

namespace App\Http\Controllers\Api\V1\User\Design;



use App\Http\Controllers\Controller;
use App\Http\Requests\Design\StoreDesignFinalizationRequest;
use App\Http\Requests\Design\StoreDesignRequest;
use App\Http\Requests\Design\UpdateDesignRequest;
use App\Http\Resources\Design\DesignFinalizationResource;
use App\Http\Resources\Design\DesignResource;
use App\Http\Resources\Design\DesignVersionResource;
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
        ]), relationsToLoad: ['product']);
        return Response::api(data: DesignResource::make($design->refresh()));
    }

    public function show($design)
    {
        $design = $this->designService->showResource($design,['media','product']);
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

    public function designFinalization(StoreDesignFinalizationRequest $request)
    {
        $designData = $this->designService->designFinalization($request);

        return Response::api(data: DesignFinalizationResource::collection($designData['syncData'])->additional([
            'sub_total' => $designData['sub_total'],
            'quantity' => $designData['quantity'],
        ]));


    }
}
