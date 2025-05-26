<?php

namespace App\Repositories\Implementations;


use App\Models\Message;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\MessageRepositoryInterface;

class MessageRepository extends BaseRepository implements MessageRepositoryInterface
{
    public function __construct(Message $message)
    {
        parent::__construct($message);
    }

}
