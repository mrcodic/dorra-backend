<?php

namespace Database\Seeders;

use App\Enums\Ai\AiGenerationTypeEnum;
use App\Models\AiStudioItem;
use Illuminate\Database\Seeder;

class AiStudioItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'key' => 'image',
                'name' => ['en' => 'Image', 'ar' => 'صورة'],
                'description' => [
                    'en' => 'Generate a custom image or standalone artwork.',
                    'ar' => 'إنشاء صورة أو عمل فني مخصص.',
                ],
                'generation_type' => AiGenerationTypeEnum::IMAGE->value,
                'credits_cost' => 1,
                'is_active' => true,
                'sort_order' => 1,
                'settings' => [
                    'prompt_instructions' => 'Create a polished standalone image or artwork that follows the product context and user answers precisely.',
                    'negative_rules' => 'unrelated text, unrelated objects, unwanted mockup',
                ],
            ],
            [
                'key' => 'logo',
                'name' => ['en' => 'Logo', 'ar' => 'لوجو'],
                'description' => [
                    'en' => 'Generate a clean professional logo.',
                    'ar' => 'إنشاء لوجو احترافي ونظيف.',
                ],
                'generation_type' => AiGenerationTypeEnum::IMAGE->value,
                'credits_cost' => 2,
                'is_active' => true,
                'sort_order' => 2,
                'settings' => [
                    'prompt_instructions' => 'Create a professional logo mark. Keep the concept clear, distinctive, scalable and visually clean. Do not place the logo on a product mockup unless explicitly requested.',
                    'negative_rules' => 'busy composition, photorealistic mockup, unrelated objects, unnecessary decorative text',
                ],
            ],
            [
                'key' => 'pattern',
                'name' => ['en' => 'Pattern', 'ar' => 'باترن'],
                'description' => [
                    'en' => 'Generate a seamless repeatable pattern.',
                    'ar' => 'إنشاء باترن متكرر ومتناسق.',
                ],
                'generation_type' => AiGenerationTypeEnum::PATTERN->value,
                'credits_cost' => 3,
                'is_active' => true,
                'sort_order' => 3,
                'settings' => [
                    'prompt_instructions' => 'Create a seamless repeatable pattern tile with clean edge continuity and balanced repetition. Follow the product context and user answers precisely.',
                    'negative_rules' => 'visible seams, broken tile edges, unwanted mockup, unrelated text',
                ],
            ],
        ];

        foreach ($items as $item) {
            AiStudioItem::query()->updateOrCreate(
                ['key' => $item['key']],
                $item
            );
        }

        // Keep historical rows for safety, but they are no longer available in guided AI.
        AiStudioItem::query()
            ->whereNotIn('key', collect($items)->pluck('key'))
            ->update(['is_active' => false]);
    }
}
