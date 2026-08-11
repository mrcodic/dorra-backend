<?php

namespace App\Observers;

use App\Jobs\RenderMockupJob;
use App\Models\BulkJobItem;
use App\Models\Design;
use App\Models\Mockup;
use App\Models\MockupGenerationJob;
use Illuminate\Support\Facades\Log;

class MockupObserver
{
    public function updated(Mockup $mockup): void
    {
        if (!$mockup->wasChanged('pre_fill_colors')) {
            return;
        }

        $oldColorsRaw = $mockup->getOriginal('pre_fill_colors');
        if (is_string($oldColorsRaw)) {
            $oldColorsRaw = json_decode($oldColorsRaw, true) ?? [];
        }

        $oldHexes = collect($oldColorsRaw ?? [])
            ->map(fn($c) => $this->normalizeHex($c))
            ->all();

        $newHexes = collect($mockup->pre_fill_colors ?? [])
            ->map(fn($c) => $this->normalizeHex($c))
            ->all();

        $addedHexes = array_diff($newHexes, $oldHexes);

        if (empty($addedHexes)) {
            return;
        }
        $mockup->refresh();
        $this->generateForNewColors($mockup, $addedHexes);
    }

    protected function generateForNewColors(Mockup $mockup, array $addedHexes)
    {
        $hexToOriginalColor = collect($mockup->pre_fill_colors ?? [])
            ->keyBy(fn($c) => $this->normalizeHex($c))
            ->all();

        $templates = $mockup->templates()->get();

        if ($templates->isEmpty()) {
            return;
        }

        $renderJobs = [];

        foreach ($templates as $template) {
            $positions = $template->pivot->positions ?? [];

            if (empty($positions)) {
                continue;
            }

            foreach ($addedHexes as $hex) {
                foreach (array_keys($positions) as $side) {
                    $renderJobs[] = [
                        'template_id' => $template->id,
                        'hex' => $hex,
                        'side' => $side,
                        'points' => $positions[$side],
                    ];
                }
            }
        }

        if (empty($renderJobs)) {
            return;
        }

        $bulkJob = MockupGenerationJob::create([
            'mockup_id' => $mockup->id,
            'status' => 'processing',
            'total_count' => count($renderJobs),
            'completed_count' => 0,
            'failed_count' => 0,
            'started_at' => now(),
        ]);

        foreach ($renderJobs as $job) {
            $item = BulkJobItem::create([
                'bulk_job_id' => $bulkJob->id,
                'template_id' => $job['template_id'],
                'color' => $hexToOriginalColor[$job['hex']] ?? $job['hex'],
                'side' => $job['side'],
                'points' => $job['points'],
                'status' => 'pending',
            ]);
            Log::info("mockup", [$mockup->load('media')->media]);
            RenderMockupJob::dispatch($bulkJob, $item, $mockup);
        }
    }

    protected function normalizeHex(string $color): string
    {
        return strtolower(ltrim(trim($color), '#'));
    }

    public function deleted(Mockup $mockup)
    {
        $templateIds = $mockup->templates->pluck('id');
        Design::whereIn('template_id', $templateIds)
            ->get()
            ->each(function ($design) {
                $design->clearMediaCollection();
                $design->forceDelete();
            });
        $mockup->clearMediaCollection();
    }
}
