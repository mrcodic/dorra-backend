<?php

namespace App\Http\Controllers\Api\V1\User\Design;



use App\Http\Controllers\Controller;
use App\Http\Requests\Design\StoreDesignRequest;
use App\Http\Requests\Design\UpdateDesignRequest;
use App\Http\Resources\DesignResource;

use App\Models\Design;
use App\Services\DesignService;
use Illuminate\Support\Facades\Response;


class DesignController extends Controller
{
    public function __construct(public DesignService $designService){}

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

    public function update(UpdateDesignRequest $request, $design)
    {
        $design = $this->designService->updateResource($request->validated(), $design);
        return Response::api(data: DesignResource::make($design->refresh()));
    }
}
