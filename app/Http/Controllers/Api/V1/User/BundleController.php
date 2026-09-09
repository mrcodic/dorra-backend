<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Bundle\BundleResource;
use App\Services\BundleService;
use Illuminate\Support\Facades\Response;

class BundleController extends Controller
{
    public function __construct(protected BundleService $bundleService)
    {
    }

    public function entryPopup()
    {
        $bundle = $this->bundleService->getEntryPopupBundle();
        return Response::api(data: $bundle ? BundleResource::make($bundle) : (object)[]);
    }
}
