<?php

namespace App\Http\Controllers\Api\V1\User\Team;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use App\Models\Design;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use App\Http\Requests\Team\{StoreTeamRequest};

use App\Services\TeamService;

class TeamController extends Controller
{
    public function __construct(public TeamService $teamService)
    {
    }

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
        return Response::api(data: TeamResource::make($this->teamService->showResource($id)));

    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'teams' => ['required', 'array'],
            'teams.*' => ['nullable', 'integer', 'exists:teams,id', function ($attribute, $value, $fail) {
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
            'teams.*' => ['nullable', 'integer', 'exists:teams,id', function ($attribute, $value, $fail) {
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
            'teams.*' => ['nullable', 'integer', 'exists:teams,id', function ($attribute, $value, $fail) {
                $team = Team::find($value);
                if ($team && $team->owner_id != auth('sanctum')->id()) {
                    $fail("The selected team does not belong to you");
                }
            }]
        ]);
        $this->teamService->bulkRestore($request->teams);
        return Response::api();
    }

    public function assignToDesign(Request $request, $teamId)
    {
        $request->validate(['designs' => ['required', 'array'],
            'designs.*' => ['required', 'string', 'exists:designs,id',
                Rule::exists('designs', 'id')->whereNull('deleted_at')],
            function ($attribute, $value, $fail) use($teamId){
                $design = Design::find($value);
                if ($design && $design->teams()->pluck('id')->contains($design->id)) {
                    $fail("The selected design already added to that team.");
                }
            }
            ]);
        $this->teamService->assignToDesign($teamId);
        return Response::api();
    }

    public function bulkDeleteDesigns(Request $request,$id)
    {
        $validatedData = $request->validate(['designs' => ['required', 'array'],
            'designs.*' => ['required', 'string', 'exists:designs,id',
                Rule::exists('designs', 'id')->whereNull('deleted_at')],
            function ($attribute, $value, $fail) use ($id) {
                $exists = Design::whereKey($value)
                    ->whereNull('deleted_at')
                    ->whereHas('teams', function ($query) use ($id) {
                        $query->where('teams.id', $id);
                    })
                    ->exists();

                if (! $exists) {
                    $fail("The selected design ({$value}) does not belong to this team or no longer exists.");
                }
            }
        ]);
        $this->teamService->bulkDeleteDesigns($validatedData,$id);
        return Response::api();


    }

}
