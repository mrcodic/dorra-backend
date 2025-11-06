<?php

namespace App\Repositories\Implementations;


use App\Models\SocialLink;
use App\Repositories\{Base\BaseRepository, Interfaces\SocialLinkRepositoryInterface,};

class SocialLinkRepository extends BaseRepository implements SocialLinkRepositoryInterface
{
    public function __construct(SocialLink $socialLink)
    {
        parent::__construct($socialLink);
    }

}
