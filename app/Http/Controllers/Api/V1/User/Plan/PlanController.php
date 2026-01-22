<?php

namespace App\Http\Controllers\Api\V1\User\Plan;

use App\Http\Controllers\Controller;

use App\Http\Resources\PlanResource;
use App\Services\PlanService;
use Illuminate\Support\Facades\Response;

class PlanController extends Controller
{
    public function __construct(public PlanService $planService)
    {
    }

    public function __invoke()
    {
        $activePlans = $this->planService->activePlans();
        return Response::api(data: PlanResource::collection($activePlans));
    }
}
