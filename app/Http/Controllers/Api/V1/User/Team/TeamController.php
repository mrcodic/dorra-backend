<?php

namespace App\Http\Controllers\Api\V1\User\Team;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\Team\{StoreTeamRequest};

use App\Services\TeamService;

class TeamController extends Controller
{
    public function __construct(public TeamService $teamService){}

    public function store(StoreTeamRequest $request)
    {
        $this->teamService->storeResource($request->validated());
        return Response::api();
    }

    public function index()
    {
        return Response::api(data: TeamResource::collection($this->teamService->userTeams()));

    }
}
