<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class SubCategoryFilter implements Filter
{
    /**
     * Apply the filter to the query.
     */

    public function __invoke(Builder $query, $value, string $property): Builder
    {
        $subcategoryIds = is_array($value) ? $value : explode(',', $value);
        return $query->whereIn('sub_category_id', $subcategoryIds);
    }
}
