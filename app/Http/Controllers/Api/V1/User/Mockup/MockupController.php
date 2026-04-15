<?php

namespace App\Http\Controllers\Api\V1\User\Mockup;


use App\Enums\Mockup\TypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\MockupResource;
use App\Models\Design;
use App\Models\Mockup;
use App\Services\MockupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;


class MockupController extends Controller
{
    public function __construct(public MockupService $mockupService)
    {
    }

    public function index(Request $request)
    {
        $data = $this->mockupService->getAll(['types'], true, perPage: 10);
        return Response::api(data: MockupResource::collection($data)->response()->getData(true));

    }


    public function show($id)
    {
        $mockup = $this->mockupService->showResource($id, ['types', 'templates', 'sideSettings']);
        return Response::api(data: MockupResource::make($mockup));

    }

    public function types()
    {
        return Response::api(data: TypeEnum::toArray());
    }

    /**
     * @throws ValidationException
     */
    public function positions(Mockup $mockup, Design $design)
    {
        if (!$design->template_id) throw ValidationException::withMessages(['message' => 'Design template not found']);
        $mockupItem = $design->template->mockups()->where('mockups.id', $mockup->id)->first();
        if (!$mockupItem) throw ValidationException::withMessages(['message' => 'Mockup is not attached to this template']);
        return Response::api(data:[
            'mockup_id' => $mockup->id,
            'template_id' => $design->template->id,
            'positions' => $mockupItem->pivot->positions,
        ]);
    }
}
