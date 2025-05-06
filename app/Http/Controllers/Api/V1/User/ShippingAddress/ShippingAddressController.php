<?php

namespace App\Http\Controllers\Api\V1\User\ShippingAddress;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShippingAddressResource;
use App\Models\ShippingAddress;
use App\Services\ShippingAddressService;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\User\ShippingAddress\{StoreShippingAddressRequest, UpdateShippingAddressRequest};


class ShippingAddressController extends Controller
{
    public function __construct(public ShippingAddressService $shippingAddressService){}

    public function index()
    {
        $shippingAddresses = $this->shippingAddressService->getUserShippingAddresses(request()->user());
        return Response::api(data: ShippingAddressResource::collection($shippingAddresses));
    }

    public function store(StoreShippingAddressRequest $request)
    {
        $shippingAddress = $this->shippingAddressService->storeResource($request->validated());
        return Response::api(data: ShippingAddressResource::make($shippingAddress));
    }

    public function show($id)
    {
        $shippingAddress = $this->shippingAddressService->showResource($id);
        return Response::api(data: ShippingAddressResource::make($shippingAddress));
    }

    public function update(UpdateShippingAddressRequest $request, ShippingAddress $shippingAddress)
    {
        $shippingAddress = $this->shippingAddressService->updateResource($request->validated(), $shippingAddress->id);
        return Response::api(data: ShippingAddressResource::make($shippingAddress));

    }

    public function destroy(ShippingAddress $shippingAddress)
    {
       $this->shippingAddressService->deleteResource($shippingAddress->id);
        return Response::api();
    }

}
