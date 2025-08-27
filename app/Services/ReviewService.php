<?php

namespace App\Services;



use App\Models\Review;
use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\ReviewRepositoryInterface;

class ReviewService extends BaseService
{
    public BaseRepositoryInterface $repository;

    public function __construct(ReviewRepositoryInterface $repository,public ProductRepositoryInterface $productRepository)
    {

        parent::__construct($repository);

    }
    public function replyReview($request, $review)
    {
      $review = $this->repository->update([
          'comment' => $request->comment,
      ],$review);
      if ($request->allFiles()) {
          handleMediaUploads($request->image,$review,"review_reply",clearExisting: true);
      }
      return $review;

    }

    public function deleteReply($review): bool
    {
        $review = $this->repository->update([
            'comment' =>null,
        ],$review);
        return (bool) $review;

    }

    public function statistics($id)
    {
        return [
            'total_reviews' => $this->repository->query()->where('reviewable_id',$id)->count(),
            'rating' => $this->productRepository->find($id)->rating,
            ];
}

}
