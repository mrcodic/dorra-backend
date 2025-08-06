<?php

namespace App\Repositories\Implementations;

use App\Models\LandingReview;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\LandingReviewRepositoryInterface;

class LandingReviewRepository extends BaseRepository implements LandingReviewRepositoryInterface
{
    public function __construct(LandingReview $landingReview)
    {
        parent::__construct($landingReview);
    }
}
