<?php

namespace App\Http\Controllers\Api\V1\User\Design;


use App\Http\Controllers\Controller;
use App\Http\Requests\Design\StoreDesignFinalizationRequest;
use App\Http\Requests\Design\StoreDesignRequest;
use App\Http\Requests\Design\UpdateDesignRequest;
use App\Http\Resources\Design\DesignFinalizationCollectionResource;
use App\Http\Resources\Design\DesignFinalizationResource;
use App\Http\Resources\Design\DesignResource;
use App\Http\Resources\Design\DesignVersionResource;
use App\Http\Resources\UserResource;
use App\Models\Design;
use App\Services\DesignService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;


class DesignController extends Controller
{
    public function __construct(public DesignService $designService)
    {
    }

    public function index()
    {
        $designs = $this->designService->getDesigns();
        return Response::api(data: DesignResource::collection($designs)->response()->getData(true));

    }

    public function store(StoreDesignRequest $request)
    {
        $design = $this->designService->storeResource($request->only([
            'template_id',
            'user_id',
            'guest_id',
            'design_data',
            'design_image',
            'current_version',
            'name',
            'description',
            'height',
            'width',
            'unit',
            'product_id'
        ]));
        $cookieData = getCookie('cookie_id');
        $cookie = $cookieData['cookie'];
        return Response::api(
            data: [
                'design' => DesignResource::make($design),
                'cookie_value' => $design->guest?->cookie_value ?? null,
            ],
        );
    }

    public function show($design)
    {
        $design = $this->designService->showResource($design, ['media', 'product','directProduct']);
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

        return Response::api(data: DesignFinalizationResource::collection($designData['syncData'])
            ->additional([
                'sub_total' => $designData['sub_total'],
                'quantity' => $designData['quantity'],
            ])->response()->getData(true)
        );
    }

    public function addQuantity(Request $request, $designId)
    {
        $request->validate([
            'quantity' => ['required_without:product_price_id', 'integer', 'min:1'],
            'product_price_id' => ['required_without:quantity', 'integer', 'exists:product_prices,id',
                function ($attribute, $value, $fail) use ($designId) {
                    $design = Design::find($designId);
                    if (!$design || !$design->product->prices->pluck('id')->contains($value)) {
                        $fail('The selected product price is not valid for the current design.');
                    }
                }
            ],
        ]);
        $this->designService->addQuantity($request, $designId);
        return Response::api();
    }

    public function priceDetails($designId)
    {
        $designData = $this->designService->priceDetails($designId);
        return Response::api(data: new DesignFinalizationCollectionResource(collect($designData)));


    }

    public function getQuantities($designId)
    {
        $quantities = $this->designService->getQuantities($designId);
        $quantities = $quantities ?: (object)[];
        return Response::api(data: $quantities);

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
