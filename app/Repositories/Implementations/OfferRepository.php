<?php

namespace App\Repositories\Implementations;

use App\Models\Offer;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\OfferRepositoryInterface;


class OfferRepository extends BaseRepository implements OfferRepositoryInterface
{
    public function __construct(Offer $offer)
    {
        parent::__construct($offer);
    }



}
