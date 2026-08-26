<?php

namespace App\Repositories\Implementations;

use App\Models\AiGuideQuestionOption;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\AiGuideQuestionOptionRepositoryInterface;

class AiGuideQuestionOptionRepository extends BaseRepository implements AiGuideQuestionOptionRepositoryInterface
{
    public function __construct(AiGuideQuestionOption $aiGuideQuestionOption)
    {
        parent::__construct($aiGuideQuestionOption);
    }

}
