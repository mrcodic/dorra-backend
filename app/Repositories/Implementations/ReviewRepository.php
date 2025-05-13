<?php

namespace App\Repositories\Implementations;


use App\Models\Review;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\ReviewRepositoryInterface;


class ReviewRepository extends BaseRepository implements ReviewRepositoryInterface
{
    public function __construct(Review $review)
    {
        parent::__construct($review);
    }



}
