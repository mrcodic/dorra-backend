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
        // Convert value to array in case it's a comma-separated string
        $subcategoryIds = is_array($value) ? $value : explode(',', $value);

        return $query->whereIn('sub_category_id', $subcategoryIds);
    }
}
