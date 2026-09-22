<?php

namespace App\Observers\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait GeneratesUniqueSlug
{
    /**
     * Generate a unique slug for the given model.
     * Supports translatable source fields (spatie/laravel-translatable).
     */
    protected function generateUniqueSlug(Model $model, string $sourceField = 'name', string $slugField = 'slug', ?string $locale = null): string
    {
        $locale = $locale ?? config('app.fallback_locale', 'en');

        $sourceValue = method_exists($model, 'getTranslation')
            ? $model->getTranslation($sourceField, $locale, false) ?: $model->getTranslation($sourceField, config('app.locale'))
            : $model->{$sourceField};

        $baseSlug = Str::slug($sourceValue);
        $slug = $baseSlug;
        $counter = 1;

        while (
        $model->newQuery()
            ->where($slugField, $slug)
            ->when($model->exists, fn ($query) => $query->where($model->getKeyName(), '!=', $model->getKey()))
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    protected function assignSlugOnCreate(Model $model, string $sourceField = 'name', string $slugField = 'slug'): void
    {
        if (empty($model->{$slugField})) {
            $model->{$slugField} = $this->generateUniqueSlug($model, $sourceField, $slugField);
        }
    }

    protected function assignSlugOnUpdate(Model $model, string $sourceField = 'name', string $slugField = 'slug'): void
    {
        // isDirty works fine with translatable json columns too
        if ($model->isDirty($sourceField) && !$model->isDirty($slugField)) {
            $model->{$slugField} = $this->generateUniqueSlug($model, $sourceField, $slugField);
        }
    }
}
