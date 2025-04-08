<?php

namespace App\Repositories\Implementations;

use App\Models\Category;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\CategoryRepositoryInterface;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $category)
    {
        parent::__construct($category);
    }
}
