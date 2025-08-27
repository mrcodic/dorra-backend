<?php

namespace App\Http\Controllers\Api\V1\User\Review;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Services\ReviewService;
use Illuminate\Support\Facades\Response;

class ReviewController extends Controller
{
    public function __construct(public ReviewService $reviewService){}

    public function store(StoreReviewRequest $request)
    {
        $this->reviewService->storeResource(array_merge(
            $request->validated(),
            ['user_id' => $request->user()->id]
        ));
        return Response::api();
    }

    public function statistics($id)
    {
        return Response::api(data: $this->reviewService->statistics($id));

    }

}
