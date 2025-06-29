<?php

namespace App\Repositories\Implementations;

use App\Models\Comment;
use App\Repositories\{Base\BaseRepository, Interfaces\CommentRepositoryInterface,};

class CommentRepository extends BaseRepository implements CommentRepositoryInterface
{
    public function __construct(Comment $comment)
    {
        parent::__construct($comment);
    }

}
