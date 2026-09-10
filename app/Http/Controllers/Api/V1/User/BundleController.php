<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\StoreBundleCartRequest;
use App\Http\Resources\Bundle\BundleResource;
use App\Services\Bundle\BundleCartService;
use App\Services\BundleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Response;

class BundleController extends Controller
{
    public function __construct(
        protected BundleService $bundleService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $bundles = $this->bundleService->getAll(
            relations: [
                'trigger.itemable',
                'rewards.itemable',
            ],
            paginate: $request->get('paginate', false)
        );

        $bundleResourceCollection = $bundles instanceof LengthAwarePaginator
            ? BundleResource::collection($bundles)->response()->getData()
            : BundleResource::collection($bundles);

        return Response::api(data: $bundleResourceCollection);
    }

    public function show($id, Request $request): JsonResponse
    {
        $bundle = $this->bundleService->showResource(
            $id,
            [
                'trigger.itemable',
                'rewards.itemable',
            ]
        );

        return Response::api(
            data: BundleResource::make($bundle)
        );
    }

    public function entryPopup(): JsonResponse
    {
        $bundle = $this->bundleService->getEntryPopupBundle();

        return Response::api(
            data: $bundle ? BundleResource::make($bundle) : (object) []
        );
    }

    public function addBundleToCart(
        StoreBundleCartRequest $request,
        BundleCartService $bundleCartService
    ): JsonResponse {
        $cart = $bundleCartService->store($request);

        return Response::api(
            data: $cart
        );
    }
}
