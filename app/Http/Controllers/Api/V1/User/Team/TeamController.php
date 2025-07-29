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
        $team = $this->teamService->storeResource($request->validated());
        return Response::api(data: TeamResource::make($team));
    }

    public function index()
    {
        return Response::api(data: TeamResource::collection($this->teamService->userTeams()));

    }

    public function destroy($id)
    {
        $this->teamService->deleteResource($id);
        return Response::api();

    }

    public function show($id)
    {
        return Response::api(data: TeamResource::make( $this->teamService->showResource($id)));

    }
}
