<?php

namespace App\Services;


use AllowDynamicProperties;
use App\Repositories\Interfaces\TeamRepositoryInterface;


#[AllowDynamicProperties] class TeamService extends BaseService
{
    public function __construct(TeamRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->query = $this->repository->query()
            ->whereBelongsTo(auth('sanctum')->user(), 'owner');

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

    public function deleteResource($id): void
    {
        $this->query->findOrFail($id)->delete();
    }

    public function showResource($id, $relations = [])
    {
        return $this->query->with(['owner', 'members'])->findOrFail($id);
    }

}
