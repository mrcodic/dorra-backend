<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReviewController extends Controller
{
    public function __construct(public ReviewService $reviewService){}

    public function replyReview(Request $request,$review)
    {
        $review =$this->reviewService->replyReview($request,$review);
        return Response::api(data:[
            'image' => $review->getFirstMediaUrl('review_reply')
        ,'comment' => $review->comment
        ]);


    }
    public function deleteReview($review)
    {
        $this->reviewService->deleteResource($review);
        return Response::api();
    }

    public function deleteReply($review)
    {
        $this->reviewService->deleteReply($review);
        return Response::api();
    }
}
