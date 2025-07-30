<?php

namespace App\Services;


use AllowDynamicProperties;
use App\Jobs\SendInvitationsJob;
use App\Repositories\Interfaces\TeamRepositoryInterface;
use Illuminate\Support\Arr;


#[AllowDynamicProperties] class TeamService extends BaseService
{
    public function __construct(TeamRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->query = $this->repository->query()
            ->whereBelongsTo(auth('sanctum')->user(), 'owner');

    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $model = $this->repository->create($validatedData);
        if (Arr::get($validatedData, 'emails')) {
            SendInvitationsJob::dispatch($model, $validatedData['emails']);
        }
        return $model->load($relationsToLoad);
}
    public function userTeams()
    {
        return $this->query
            ->withCount('designs')
            ->with(['owner', 'members.media' => function ($query) {
                $query->where('collection_name', 'users');
            },])
            ->when(request()->filled('owner_id'), fn($q) => $q->where('owner_id', request('owner_id')))
            ->orderBy('created_at', request('date', 'desc'))
            ->paginate();
    }



    public function showResource($id, $relations = [])
    {
        return $this->query->with(['owner', 'members'])->findOrFail($id);
    }
    public function assignToDesign($teamId): void
    {
        $team = $this->repository->find($teamId);
        $team->designs()->syncWithoutDetaching(request()->designs);
    }
    public function bulkForceResources($ids)
    {
        return $this->repository->query()->withTrashed()->whereIn('id', $ids)->forceDelete();
    }

    public function trash()
    {
        return $this->repository->query()
            ->onlyTrashed()
            ->withCount('designs')
            ->with(['owner', 'members.media' => function ($query) {
                $query->where('collection_name', 'users');
            },])
            ->whereOwnerId(auth('sanctum')->id())
            ->get();
    }

}
