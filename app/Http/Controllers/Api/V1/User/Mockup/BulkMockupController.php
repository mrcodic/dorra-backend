<?php

namespace App\Http\Controllers\Api\V1\User\Mockup;

use App\Http\Controllers\Controller;
use App\Jobs\RenderMockupJob;
use App\Models\BulkJobItem;
use App\Models\Mockup;
use App\Models\MockupGenerationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BulkMockupController extends Controller
{
    public function generateBulk(Request $request, Mockup $mockup)
    {
        $request->validate([
            'template_ids'     => 'required|array|min:1',
            'template_ids.*'   => 'string|exists:templates,id',
            'colors'           => 'required|array|min:1',
            'colors.*'         => 'string',
            'positions'        => ['required', 'array'],
            'positions.*.name' => ['required', 'string', 'max:100',
                Rule::in($mockup->types->map(fn($t) => $t->value->key())->toArray()),
            ],
            'positions.*.p1x'  => ['required', 'numeric'],
            'positions.*.p1y'  => ['required', 'numeric'],
            'positions.*.p2x'  => ['required', 'numeric'],
            'positions.*.p2y'  => ['required', 'numeric'],
            'positions.*.p3x'  => ['required', 'numeric'],
            'positions.*.p3y'  => ['required', 'numeric'],
            'positions.*.p4x'  => ['required', 'numeric'],
            'positions.*.p4y'  => ['required', 'numeric'],
        ]);

        $templateIds = $request->input('template_ids');

        $colors = collect($request->input('colors'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $positions = collect($request->input('positions'))
            ->keyBy('name')
            ->map(fn($p) => [
                'p1x' => $p['p1x'], 'p1y' => $p['p1y'],
                'p2x' => $p['p2x'], 'p2y' => $p['p2y'],
                'p3x' => $p['p3x'], 'p3y' => $p['p3y'],
                'p4x' => $p['p4x'], 'p4y' => $p['p4y'],
            ])
            ->toArray();

        $sides = array_keys($positions);

        $newColorsNormalized = collect($colors)
            ->map(fn($c) => strtolower(ltrim(trim($c), '#')))
            ->all();

        // -----------------------------------------------------------------------
        // Load all currently attached templates with pivot data (before sync)
        // -----------------------------------------------------------------------
        $existingTemplates          = $mockup->templates()->get()->keyBy('id');
        $previouslyAttachedIds      = $existingTemplates->keys()->map(fn($id) => (string) $id)->toArray();
        $incomingIds                = collect($templateIds)->map(fn($id) => (string) $id)->toArray();

        $removedTemplateIds         = array_diff($previouslyAttachedIds, $incomingIds);
        $newTemplateIds             = array_diff($incomingIds, $previouslyAttachedIds);
        $alreadyAttachedTemplateIds = array_intersect($incomingIds, $previouslyAttachedIds);

        // -----------------------------------------------------------------------
        // 1. Delete ALL generated media for removed (deselected) templates
        // -----------------------------------------------------------------------
        foreach ($removedTemplateIds as $removedTemplateId) {
            $mockup->media()
                ->where('collection_name', 'generated_mockups')
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [(string) $removedTemplateId])
                ->get()
                ->each(fn($media) => $media->delete());
        }

        // -----------------------------------------------------------------------
        // 2. For already-attached templates:
        //    - Delete media for removed colors
        //    - Keep media for existing colors
        //    - Track new colors that need rendering
        //    - If positions changed → re-render ALL colors
        // -----------------------------------------------------------------------

        // $renderJobs = [ ['template_id' => ..., 'color' => ..., 'sides' => [...]] ]
        $renderJobs = [];

        foreach ($alreadyAttachedTemplateIds as $templateId) {
            $existingTemplate  = $existingTemplates->get($templateId);
            $pivot             = $existingTemplate->pivot;

            $previousColors = collect($pivot->colors ?? [])
                ->map(fn($c) => strtolower(ltrim(trim($c), '#')))
                ->all();

            $previousPositions = $pivot->positions ?? [];
            $positionsChanged  = json_encode($previousPositions) !== json_encode($request->input('positions'));

            $removedHexes = array_diff($previousColors, $newColorsNormalized);
            $addedHexes   = array_diff($newColorsNormalized, $previousColors);
            $keptHexes    = array_intersect($previousColors, $newColorsNormalized);

            // Delete media for removed colors
            foreach ($removedHexes as $hex) {
                foreach ($sides as $side) {
                    $mockup->media()
                        ->where('collection_name', 'generated_mockups')
                        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [(string) $templateId])
                        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.side')) = ?", [$side])
                        ->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex'))) = ?", [$hex])
                        ->get()
                        ->each(fn($media) => $media->delete());
                }
            }

            // Clear model_color on pivot if that color was removed
            $modelColorHex = $pivot?->model_color ? strtolower(ltrim(trim($pivot->model_color), '#')) : null;
            if ($modelColorHex && in_array($modelColorHex, $removedHexes)) {
                $mockup->templates()->updateExistingPivot($templateId, ['model_color' => null]);
            }

            if ($positionsChanged) {
                // Positions changed → re-render ALL colors (delete kept ones too)
                foreach ($keptHexes as $hex) {
                    foreach ($sides as $side) {
                        $mockup->media()
                            ->where('collection_name', 'generated_mockups')
                            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [(string) $templateId])
                            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.side')) = ?", [$side])
                            ->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex'))) = ?", [$hex])
                            ->get()
                            ->each(fn($media) => $media->delete());
                    }
                }
                // Queue all colors for re-render
                foreach ($newColorsNormalized as $hex) {
                    $renderJobs[] = ['template_id' => $templateId, 'hex' => $hex];
                }
            } else {
                // Positions unchanged → only render NEW colors, keep existing media
                foreach ($addedHexes as $hex) {
                    $renderJobs[] = ['template_id' => $templateId, 'hex' => $hex];
                }
                // Kept colors → no action needed, media already exists ✅
            }
        }

        // -----------------------------------------------------------------------
        // 3. New templates → render all colors
        // -----------------------------------------------------------------------
        foreach ($newTemplateIds as $templateId) {
            foreach ($newColorsNormalized as $hex) {
                $renderJobs[] = ['template_id' => $templateId, 'hex' => $hex];
            }
        }

        // -----------------------------------------------------------------------
        // 4. Sync pivot data (colors + positions) for all incoming templates
        // -----------------------------------------------------------------------
        $syncData = [];
        foreach ($templateIds as $templateId) {
            $syncData[$templateId] = [
                'colors'    => $colors,
                'positions' => $request->input('positions'),
            ];
        }
        $mockup->templates()->sync($syncData);
        $mockup->update(['colors' => $colors]);

        // -----------------------------------------------------------------------
        // 5. Dispatch jobs only for what actually needs rendering
        // -----------------------------------------------------------------------

        // Map normalized hex back to original color value from request
        $hexToOriginalColor = collect($colors)
            ->keyBy(fn($c) => strtolower(ltrim(trim($c), '#')))
            ->all();

        $totalCount = count($renderJobs) * count($sides);

        $bulkJob = MockupGenerationJob::create([
            'mockup_id'       => $mockup->id,
            'status'          => 'pending',
            'total_count'     => $totalCount,
            'completed_count' => 0,
            'failed_count'    => 0,
        ]);

        foreach ($renderJobs as $job) {
            $originalColor = $hexToOriginalColor[$job['hex']] ?? $job['hex'];

            foreach ($sides as $side) {
                $item = BulkJobItem::create([
                    'bulk_job_id' => $bulkJob->id,
                    'template_id' => $job['template_id'],
                    'color'       => $originalColor,
                    'side'        => $side,
                    'points'      => $positions[$side],
                    'status'      => 'pending',
                ]);

                RenderMockupJob::dispatch($bulkJob, $item, $mockup);
            }
        }

        $bulkJob->update([
            'status'     => $totalCount > 0 ? 'processing' : 'completed',
            'started_at' => now(),
        ]);

        return Response::api(data: [
            'success'           => true,
            'bulk_job_id'       => $bulkJob->id,
            'total_count'       => $totalCount,
            'sides'             => $sides,
            'colors'            => $colors,
            'rendered_jobs'     => count($renderJobs),
        ]);
    }

    public function status($id)
    {
        $job     = MockupGenerationJob::findOrFail($id);
        $elapsed = now()->diffInSeconds($job->started_at);
        $rate    = $job->completed_count / max($elapsed, 1);
        $remaining = ($job->total_count - $job->completed_count) / max($rate, 0.01);

        return Response::api(data: [
            'id'                          => $job->id,
            'status'                      => $job->status,
            'total_count'                 => $job->total_count,
            'completed_count'             => $job->completed_count,
            'failed_count'                => $job->failed_count,
            'percent'                     => round(($job->completed_count / max($job->total_count, 1)) * 100, 1),
            'estimated_remaining_seconds' => (int) $remaining,
            'started_at'                  => $job->started_at,
        ]);
    }

    public function cancel($id)
    {
        $job = MockupGenerationJob::findOrFail($id);
        $job->update(['status' => 'cancelled']);

        BulkJobItem::where('bulk_job_id', $id)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        $mockup = Mockup::findOrFail($job->mockup_id);

        $items = BulkJobItem::where('bulk_job_id', $id)
            ->where('status', 'completed')
            ->get();

        foreach ($items as $item) {
            $hex = strtolower(ltrim(trim($item->color), '#'));

            $mockup->media()
                ->where('collection_name', 'generated_mockups')
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [(string) $item->template_id])
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.side')) = ?", [$item->side])
                ->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex'))) = ?", [$hex])
                ->get()
                ->each(fn($media) => $media->delete());
        }

        $templateIds = BulkJobItem::where('bulk_job_id', $id)
            ->pluck('template_id')
            ->unique()
            ->all();

        $mockup->templates()->detach($templateIds);

        return Response::api(data: [
            'success' => true,
            'message' => "Cancelled and rolled back {$job->completed_count} generated images.",
        ]);
    }

    public function retry(MockupGenerationJob $bulkJob)
    {
        $mockup = $bulkJob->mockup;

        if (!$mockup) {
            throw ValidationException::withMessages([
                'mockup' => ['Mockup not found.'],
            ]);
        }

        if (!in_array($bulkJob->status, ['failed', 'completed_with_errors', 'cancelled'])) {
            throw ValidationException::withMessages([
                'status' => ['Only failed or completed_with_errors jobs can be retried.'],
            ]);
        }

        $failedItems = BulkJobItem::where('bulk_job_id', $bulkJob->id)
            ->where('status', 'failed')
            ->get();

        if ($failedItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => ['No failed items to retry.'],
            ]);
        }

        BulkJobItem::where('bulk_job_id', $bulkJob->id)
            ->where('status', 'failed')
            ->update([
                'status'        => 'pending',
                'error_message' => null,
                'output_path'   => null,
            ]);

        $bulkJob->update([
            'status'       => 'processing',
            'failed_count' => 0,
            'total_count'  => $bulkJob->completed_count + $failedItems->count(),
            'completed_at' => null,
            'started_at'   => now(),
        ]);

        foreach ($failedItems as $item) {
            RenderMockupJob::dispatch($bulkJob, $item->fresh(), $mockup);
        }

        return Response::api(data: [
            'success'     => true,
            'retried'     => $failedItems->count(),
            'bulk_job_id' => $bulkJob->id,
        ]);
    }
}
