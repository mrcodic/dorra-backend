<?php

namespace App\Http\Controllers\Api\V1\User\Design;


use App\Http\Controllers\Controller;
use App\Http\Requests\Design\StoreDesignFinalizationRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\Design\{StoreDesignRequest, UpdateDesignRequest};
use App\Http\Resources\Design\{DesignResource, DesignFinalizationResource, DesignVersionResource};
use App\Http\Resources\UserResource;
use App\Models\Design;
use App\Services\DesignService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Response;


class DesignController extends Controller
{
    public function __construct(public DesignService $designService)
    {
    }

    public function index()
    {
        $designs = $this->designService->getDesigns();
        $resource = $designs instanceof LengthAwarePaginator ?DesignResource::collection($designs)->response()->getData(true) :DesignResource::collection($designs);
        return Response::api(data: $resource);

    }

    public function store(StoreDesignRequest $request)
    {
        $design = $this->designService->storeResource($request->only([
            'template_id',
            'user_id',
            'guest_id',
            'design_data',
            'design_back_data',
            'design_image',
            'current_version',
            'name',
            'description',
            'height',
            'width',
            'unit',
            'designable_id',
            'designable_type',
            'specs',
            'product_price_id',
            'orientation',
            'dimension_id'
        ]));

        return Response::api(
            data: [
                'design' => DesignResource::make($design),
                'cookie_value' => $design->guest?->cookie_value,
            ],
        );
    }

    public function show($design)
    {
        $design = $this->designService->showResource($design, ['media', 'designable','designable.prices','designable.specifications.options']);
        return Response::api(data: DesignResource::make($design));
    }

    public function update(UpdateDesignRequest $request, $design)
    {
        $design = $this->designService->updateResource($request->validated(), $design);
        return Response::api(data: DesignResource::make($design->refresh()));
    }

    public function assignToTeam(Request $request, $designId)
    {
        $request->validate(['teams' => ['required', 'array'],
            'teams.*' => ['required', 'integer',
            'exists:teams,id',
            Rule::exists('teams', 'id')->whereNull('deleted_at')]
        ]);
        $this->designService->assignToTeam($designId);
        return Response::api();
    }

    public function getDesignVersions($designId)
    {
        $designVersions = $this->designService->getDesignVersions($designId);
        return Response::api(data: DesignVersionResource::collection($designVersions));
    }

    public function designFinalization(StoreDesignFinalizationRequest $request)
    {
        $designData = $this->designService->designFinalization($request);
        return Response::api(data: DesignFinalizationResource::collection($designData['syncData'])
            ->additional([
                'sub_total' => $designData['sub_total'],
                'quantity' => $designData['quantity'],
            ])->response()->getData(true)
        );
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'designs' => ['required', 'array'],
            'designs.*' => ['nullable', 'string', 'exists:designs,id', function ($attribute, $value, $fail) {
                $design = Design::find($value);
                if ($design && $design->user_id != auth('sanctum')->id()) {
                    $fail("The selected design does not belong to you");
                }
            }]
        ]);
        $this->designService->bulkDeleteResources($request->designs);
        return Response::api();
    }

    public function bulkForceDelete(Request $request)
    {
        $request->validate([
            'designs' => ['required', 'array'],
            'designs.*' => ['nullable', 'string', 'exists:designs,id', function ($attribute, $value, $fail) {
                $design = Design::find($value);
                if ($design && $design->user_id != auth('sanctum')->id()) {
                    $fail("The selected design does not belong to you");
                }
            }]
        ]);
        $this->designService->bulkForceResources($request->designs);
        return Response::api();
    }

    public function bulkRestore(Request $request)
    {
        $request->validate([
            'designs' => ['required', 'array'],
            'designs.*' => ['nullable', 'string', 'exists:designs,id', function ($attribute, $value, $fail) {
                $design = Design::find($value);
                if ($design && $design->user_id != auth('sanctum')->id()) {
                    $fail("The selected design does not belong to you");
                }
            }]
        ]);
        $this->designService->bulkRestore($request->designs);
        return Response::api();
    }

    public function owners()
    {
        $owners = $this->designService->owners();
        return Response::api(data: UserResource::collection($owners));


    }

}
