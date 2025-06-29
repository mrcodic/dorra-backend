<?php

namespace App\Services;

use App\Models\{Design,Template};
use App\Repositories\Interfaces\CommentRepositoryInterface;
use Illuminate\Http\Request;


class CommentService extends BaseService
{
    public function __construct(CommentRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getComments(Request $request)
    {
        return $this->repository->query()
            ->when(
                $request->filled('commentable_type') && $request->filled('commentable_id'),
                function ($q) use ($request) {
                    $modelClass = commentableModelClass($request->commentable_type);
                    if ($modelClass) {
                        $q->where('commentable_type', $modelClass)
                            ->where('commentable_id', $request->commentable_id);
                    }
                }
            )
            ->get();
    }

}
