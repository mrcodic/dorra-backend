<?php

namespace App\Http\Controllers\Api\V1\User\Industry;

use App\Http\Controllers\Controller;

use App\Http\Resources\IndustryResource;
use App\Services\IndustryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;



class IndustryController extends Controller
{
    public function __construct(public IndustryService $industryService)
    {
    }

    public function index()
    {
        $industries = $this->industryService->getAll();
        return Response::api(data: IndustryResource::collection($industries));
    }


    public function getSubIndustries(Request $request)
    {
        $industries = $this->industryService->getSubIndustries($request);
        return Response::api(data: IndustryResource::collection($industries));
    }


}
