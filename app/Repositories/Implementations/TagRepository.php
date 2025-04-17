<?php

namespace App\Repositories\Implementations;

use App\Models\State;
use App\Models\Tag;
use App\Repositories\{Base\BaseRepository, Interfaces\TagRepositoryInterface};


class TagRepository extends BaseRepository implements TagRepositoryInterface
{
    public function __construct(Tag $tag)
    {
        parent::__construct($tag);
    }



}
