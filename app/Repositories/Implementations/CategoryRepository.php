<?php

namespace App\Repositories\Implementations;

use App\Models\Category;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $category)
    {
        parent::__construct($category);
    }

    public function getWithFilters(): Collection
    {
        return QueryBuilder::for(Category::class)
            ->whereNotNull('parent_id')
            ->allowedFilters([
                AllowedFilter::exact('parent_id'),
            ])
            ->get();
    }

}
