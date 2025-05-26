<?php

namespace App\Repositories\Implementations;


use App\Models\Faq;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\FaqRepositoryInterface;

class FaqRepository extends BaseRepository implements FaqRepositoryInterface
{
    public function __construct(Faq $fqa)
    {
        parent::__construct($fqa);
    }

}
