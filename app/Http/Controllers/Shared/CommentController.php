<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\CommentRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\MediaResource;
use App\Models\Admin;
use App\Services\CommentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class CommentController extends Controller
{

    public function __construct(public CommentService $commentService)
    {
    }

    public function index(Request $request)
    {
        $comments = $this->commentService->getComments($request);
        return Response::api(data: $comments ? CommentResource::collection($comments->load(['replies.owner', 'owner'])) : collect([]),);

    }

    public function store(CommentRequest $request)
    {
        $comment = $this->commentService->storeResource($request->all());
        return Response::api(data: CommentResource::make($comment));
    }

    public function destroy($id)
    {
        $this->commentService->deleteResource($id);
        return Response::api();
    }
}
