<?php

namespace App\Repositories\Implementations;


use App\Models\Faq;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\FlagRepositoryInterface;

class FlagRepository extends BaseRepository implements FlagRepositoryInterface
{
    public function __construct(Faq $fqa)
    {
        parent::__construct($fqa);
    }

}
