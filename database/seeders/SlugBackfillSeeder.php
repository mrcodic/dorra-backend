<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SlugBackfillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->backfillSlugs(Product::class, 'name');
        $this->backfillSlugs(Category::class, 'name');
    }

    /**
     * Generate unique slugs for all rows missing one.
     */
    private function backfillSlugs(string $model, string $sourceColumn): void
    {
        $model::whereNull('slug')
            ->orWhere('slug', '')
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($model, $sourceColumn) {
                foreach ($rows as $row) {
                    $base = Str::slug($row->{$sourceColumn});
                    $slug = $base;
                    $i = 1;

                    while ($model::where('slug', $slug)->where('id', '!=', $row->id)->exists()) {
                        $slug = "{$base}-{$i}";
                        $i++;
                    }

                    $row->update(['slug' => $slug]);
                }
            });

        $this->command->info("Slugs backfilled for {$model}.");
    }
}
