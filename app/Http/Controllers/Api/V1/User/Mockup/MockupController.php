<?php

namespace App\Http\Controllers\Api\V1\User\Mockup;


use App\Enums\Mockup\TypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\MockupResource;
use App\Services\MockupService;
use Illuminate\Support\Facades\Response;


class MockupController extends Controller
{
    public function __construct(public MockupService $mockupService)
    {
    }

    public function index()
    {
        $data = $this->mockupService->getAll(['types'], true, perPage: request('per_page', 10));
        return Response::api(data: MockupResource::collection($data->load('types'))->response()->getData(true));

    }


    public function show($id)
    {
        $mockup = $this->mockupService->showResource($id);
        return Response::api(data: MockupResource::make($mockup));

    }
    public function types()
    {
        return Response::api(data: TypeEnum::toArray());
    }
}
