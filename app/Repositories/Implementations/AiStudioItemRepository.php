<?php

namespace App\Repositories\Implementations;

use App\Models\AiStudioItem;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\AiStudioItemRepositoryInterface;

class AiStudioItemRepository extends BaseRepository implements AiStudioItemRepositoryInterface
{
    public function __construct(AiStudioItem $aiStudioItem)
    {
        parent::__construct($aiStudioItem);
    }

}
