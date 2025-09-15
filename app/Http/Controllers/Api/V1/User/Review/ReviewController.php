<?php

namespace App\Http\Controllers\Api\V1\User\Review;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Resources\MediaResource;
use App\Http\Resources\ReviewResource;
use App\Services\ReviewService;
use Illuminate\Http\Request;
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

    public function show(Request $request,$productId)
    {
        $reviews = $this->reviewService->productReviews($request, $productId);
        $allMedia = $reviews->flatMap(function ($review) {
            return $review->getMedia('reviews');
        });
        return Response::api(data: [
            'reviews' => ReviewResource::collection($reviews),
            'all_reviews_images' => MediaResource::collection($allMedia),
            'user_already_reviewed' => $reviews->where('user_id', auth('sanctum')->id())->isNotEmpty(),

        ]);
    }
    public function statistics($id)
    {
        return Response::api(data: $this->reviewService->statistics($id));

    }

}
