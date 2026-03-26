<?php

namespace App\Jobs;

use App\Models\Mockup;
use App\Models\Template;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandleMockupFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Queueable;

    public function __construct(
        public Mockup $mockup,
        public $type = 'update',
    ) {}

    public function handle(): void
    {
        $model = $this->mockup;

        foreach ($model->templates as $template) {
            $templateId = $template->id;

            $newColors = $this->normalizeColors($template->pivot->colors ?? []);

            $oldMockups = $this->getRelatedMockups($model, $templateId);

            $oldColors = $this->extractOldColors($oldMockups, $templateId);

            $allColors = $this->mergeColors($newColors, $oldColors);

            if ($this->type === 'create') {
                $this->syncNewMockupColors($model, $templateId, $newColors, $allColors);
            }

            // 🔥 dispatch بدلاً من render مباشر
            $this->dispatchRenderJobs($model, $template, $allColors);

            foreach ($oldMockups as $oldMockup) {
                $this->processOldMockup($oldMockup, $template, $templateId, $allColors);
            }
        }
    }

    // ================= HELPERS =================

    private function normalizeColors(array $colors)
    {
        return collect($colors)
            ->map(fn($c) => strtolower($c))
            ->filter()
            ->unique();
    }

    private function getRelatedMockups(Mockup $model, string $templateId)
    {
        return Mockup::query()
            ->where('category_id', $model->category_id)
            ->whereKeyNot($model->id)
            ->whereHas('templates', fn($q) => $q->where('templates.id', $templateId))
            ->with(['templates', 'types'])
            ->get();
    }

    private function extractOldColors($mockups, string $templateId)
    {
        return $mockups
            ->flatMap(fn($m) => $m->templates->firstWhere('id', $templateId)?->pivot->colors ?? [])
            ->map(fn($c) => strtolower($c))
            ->filter()
            ->unique();
    }

    private function mergeColors($new, $old): array
    {
        return $new->merge($old)->unique()->values()->all();
    }

    private function syncNewMockupColors(Mockup $model, string $templateId, $newColors, array $allColors)
    {
        $missing = collect($allColors)->diff($newColors)->values()->all();

        $model->templates()->updateExistingPivot($templateId, [
            'colors' => array_values(array_unique(
                array_merge($newColors->all(), $missing)
            )),
        ]);
    }

    private function processOldMockup(Mockup $mockup, Template $template, string $templateId, array $allColors)
    {
        $oldTemplate = $mockup->templates->firstWhere('id', $templateId);
        if (!$oldTemplate) return;

        $oldColors = collect($oldTemplate->pivot->colors ?? [])->filter()->values()->all();

        $missing = collect($allColors)->diff($oldColors)->values()->all();

        if (!empty($missing)) {
            $mockup->templates()->updateExistingPivot($templateId, [
                'colors' => array_values(array_unique(array_merge($oldColors, $missing))),
            ]);
        }

        $this->dispatchRenderJobs(
            $mockup,
            $oldTemplate,
            array_merge($oldColors, $missing)
        );
    }

    // ================= DISPATCH =================

    private function dispatchRenderJobs(Mockup $mockup, Template $template, array $colors): void
    {
        if (empty($colors)) return;

        $mockup->loadMissing(['types']);

        foreach ($colors as $hex) {
            foreach ($mockup->types as $type) {

                RenderMockupUnitJob::dispatch(
                    $mockup->id,
                    $template->id,
                    $type->value->name,
                    $hex
                );

            }
        }
    }
}
