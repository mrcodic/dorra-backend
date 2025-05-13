<?php

namespace App\Services;



use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\ReviewRepositoryInterface;

class ReviewService extends BaseService
{
    public BaseRepositoryInterface $repository;

    public function __construct(ReviewRepositoryInterface $repository)
    {

        parent::__construct($repository);

    }
    public function replyReview($request, $review): bool
    {
      $review = $this->repository->update([
          'comment' => $request->comment,
      ],$review);
      if ($request->allFiles()) {
          handleMediaUploads($request->image,$review,"review_reply",clearExisting: true);
      }
      return (bool) $review;

    }

    public function deleteReply($review): bool
    {
        $review = $this->repository->update([
            'comment' =>null,
        ],$review);
        return (bool) $review;

    }



}
