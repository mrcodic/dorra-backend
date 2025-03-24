<?php

namespace App\Repositories\Implementations;

use App\Models\SocialAccount;
use App\Repositories\{Interfaces\SocialAccountRepositoryInterface,Base\BaseRepository};

class SocialAccountRepository extends BaseRepository implements SocialAccountRepositoryInterface
{
    public function __construct(SocialAccount $socialAccount)
    {
        parent::__construct($socialAccount);
    }

    public function updateOrCreate(array $attributes, array $values)
    {
        return $this->model->updateOrCreate($attributes, $values);
    }
}
