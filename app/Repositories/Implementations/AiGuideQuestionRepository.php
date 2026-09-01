<?php

namespace App\Repositories\Implementations;

use App\Models\AiGuideQuestion;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\AiGuideQuestionRepositoryInterface;

class AiGuideQuestionRepository extends BaseRepository implements AiGuideQuestionRepositoryInterface
{
    public function __construct(AiGuideQuestion $aiGuideQuestion)
    {
        parent::__construct($aiGuideQuestion);
    }

}
