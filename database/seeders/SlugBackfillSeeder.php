<?php

namespace Database\Seeders;

use App\Models\Bundle;
use App\Models\Product;
use App\Models\Category;
use App\Models\Template;
use App\Observers\Traits\GeneratesUniqueSlug;
use Illuminate\Database\Seeder;

class SlugBackfillSeeder extends Seeder
{
    use GeneratesUniqueSlug;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->backfillSlugs(Product::class, 'name');
        $this->backfillSlugs(Category::class, 'name');
        $this->backfillSlugs(Template::class, 'name');
        $this->backfillSlugs(Bundle::class, 'name');
    }

    /**
     * Generate unique slugs for all rows missing one.
     */
    private function backfillSlugs(string $model, string $sourceColumn): void
    {
        $count = 0;

        $model::where(function ($query) {
            $query->whereNull('slug')->orWhere('slug', '');
        })
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($model, $sourceColumn, &$count) {
                foreach ($rows as $row) {
                    $row->slug = $this->generateUniqueSlug($row, $sourceColumn);
                    $row->saveQuietly();
                    $count++;
                }
            });

        $this->command->info("Slugs backfilled for {$model}: {$count} row(s).");
    }
}
