<?php

namespace App\Repositories\Implementations;

use App\Models\Invitation;
use App\Repositories\{Base\BaseRepository, Interfaces\InvitationRepositoryInterface};


class InvitationRepository extends BaseRepository implements InvitationRepositoryInterface
{
    public function __construct(Invitation $invitation)
    {
        parent::__construct($invitation);
    }
}
