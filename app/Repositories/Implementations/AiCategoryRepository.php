<?php

namespace App\Repositories\Implementations;

use App\Models\AiCategory;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\AiCategoryRepositoryInterface;

class AiCategoryRepository extends BaseRepository implements AiCategoryRepositoryInterface
{
    public function __construct(AiCategory $aiCategory)
    {
        parent::__construct($aiCategory);
    }
}
