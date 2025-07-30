<?php

namespace App\Http\Controllers\Api\V1\User\Team;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use Illuminate\Http\Request;
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
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'teams' => ['required', 'array'],
            'teams.*' => ['nullable', 'string', 'exists:teams,id', function ($attribute, $value, $fail) {
                $team = Team::find($value);
                if ($team && $team->owner_id != auth('sanctum')->id()) {
                    $fail("The selected team does not belong to you");
                }
            }]
        ]);
        $this->teamService->bulkDeleteResources($request->teams);
        return Response::api();
    }

    public function bulkForceDelete(Request $request)
    {
        $request->validate([
            'teams' => ['required', 'array'],
            'teams.*' => ['nullable', 'string', 'exists:teams,id', function ($attribute, $value, $fail) {
                $team = Team::find($value);
                if ($team && $team->owner_id != auth('sanctum')->id()) {
                    $fail("The selected team does not belong to you");
                }
            }]
        ]);
        $this->teamService->bulkForceResources($request->teams);
        return Response::api();
    }

    public function bulkRestore(Request $request)
    {
        $request->validate([
            'teams' => ['required', 'array'],
            'teams.*' => ['nullable', 'string', 'exists:teams,id', function ($attribute, $value, $fail) {
                $team = Team::find($value);
                if ($team && $team->owner_id != auth('sanctum')->id()) {
                    $fail("The selected team does not belong to you");
                }
            }]
        ]);
        $this->teamService->bulkRestore($request->teams);
        return Response::api();
    }


}
