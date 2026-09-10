<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Bundle\BundleResource;
use App\Services\BundleService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Response;

class BundleController extends Controller
{
    public function __construct(protected BundleService $bundleService)
    {
    }

    public function index(Request $request)
    {
        $bundles = $this->bundleService->getAll(relations: ['trigger','rewards'], paginate: $request->get('paginate',false));
        $bundleResourceCollection = $bundles instanceof LengthAwarePaginator ?
            BundleResource::collection($bundles)->response()->getData()
            : BundleResource::collection($bundles);
        return Response::api(data: $bundleResourceCollection);
    }
    public function show($id, Request $request)
    {
        $bundle = $this->bundleService->showResource($id,['trigger','rewards']);
        return Response::api(data: BundleResource::make($bundle));
    }
    public function entryPopup()
    {
        $bundle = $this->bundleService->getEntryPopupBundle();
        return Response::api(data: $bundle ? BundleResource::make($bundle) : (object)[]);
    }
}
