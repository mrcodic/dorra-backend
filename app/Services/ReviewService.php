<?php

namespace App\Services;


use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\Request;

class ReviewService extends BaseService
{
    public BaseRepositoryInterface $repository;

    public function __construct(ReviewRepositoryInterface $repository, public ProductRepositoryInterface $productRepository)
    {

        parent::__construct($repository);

    }

    public function replyReview($request, $review)
    {
        $review = $this->repository->update([
            'comment' => $request->comment,
        ], $review);
        if ($request->allFiles()) {
            handleMediaUploads($request->image, $review, "review_reply", clearExisting: true);
        }
        return $review;

    }

    public function deleteReply($review): bool
    {
        $review = $this->repository->update([
            'comment' => null,
        ], $review);
        return (bool)$review;

    }

    public function statistics(int $id): array
    {
        $stats = $this->repository->query()
            ->select([])
            ->selectRaw('COUNT(*) as total_reviews')
            ->selectRaw('AVG(rating) as rating')
            ->selectRaw('SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_stars')
            ->selectRaw('SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_stars')
            ->selectRaw('SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_stars')
            ->selectRaw('SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_stars')
            ->selectRaw('SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star')
            ->where('reviewable_id', $id)
            ->first();


        return [
            'statistics' => [
                'total_reviews' => (int)$stats->total_reviews,
                'rating' => round((float)$stats->rating, 2),
                '5_stars' => (int)$stats->five_stars,
                '4_stars' => (int)$stats->four_stars,
                '3_stars' => (int)$stats->three_stars,
                '2_stars' => (int)$stats->two_stars,
                '1_stars' => (int)$stats->one_star,
            ],
        ];
    }

    public function productReviews($id, Request $request)
    {
        return $this->repository->query()
            ->with(['user', 'media'])
            ->orderBy('created_at', request('date', 'desc'))
            ->where('reviewable_id', $id)
            ->where('reviewable_type', $request->type == 'product' ? Category::class : Product::class)
            ->get();

    }


}
