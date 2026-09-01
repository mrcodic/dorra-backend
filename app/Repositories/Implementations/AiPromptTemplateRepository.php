<?php

namespace App\Repositories\Implementations;

use App\Models\AiPromptTemplate;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\AiPromptTemplateRepositoryInterface;

class AiPromptTemplateRepository extends BaseRepository implements AiPromptTemplateRepositoryInterface
{
    public function __construct(AiPromptTemplate $aiPromptTemplate)
    {
        parent::__construct($aiPromptTemplate);
    }
}
