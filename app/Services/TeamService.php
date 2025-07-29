<?php

namespace App\Services;


use App\Repositories\Interfaces\TeamRepositoryInterface;


class TeamService extends BaseService
{
    public function __construct(TeamRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }
    public function userTeams()
    {
        return $this->repository->query()
            ->whereBelongsTo(auth('sanctum')->user(),'owner')
            ->withCount('designs')
            ->with(['owner'])
            ->latest()
            ->paginate();
    }

}
