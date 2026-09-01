<?php

namespace App\Http\Controllers\Api\V1\User\Mockup;

use App\Http\Controllers\Controller;
use App\Jobs\RenderMockupJob;
use App\Models\BulkJobItem;
use App\Models\Mockup;
use App\Models\MockupGenerationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BulkMockupController extends Controller
{
    public function generateBulk(Request $request, Mockup $mockup)
    {
        $type = $request->input('type', 'bulk');
        $colorsProvided = $request->has('colors');
        $request->validate(['type' => ['sometimes', 'string', Rule::in(['bulk', 'single'])],
            'template_ids' => ['required', 'array', 'min:1', $type === 'single' ? 'max:1' : ''],
            'template_ids.*' => ['string', 'exists:templates,id'],
            'colors' => ['sometimes', 'nullable', 'array'], 'colors.*' => ['nullable', 'string'],
            'positions' => ['required', 'array'],
            'positions.*.name' => ['required', 'string', 'max:100',
                Rule::in($mockup->types->map(fn($t) => $t->value->key())->toArray())], 'positions.*.p1x' => ['required', 'numeric'], 'positions.*.p1y' => ['required', 'numeric'], 'positions.*.p2x' => ['required', 'numeric'], 'positions.*.p2y' => ['required', 'numeric'], 'positions.*.p3x' => ['required', 'numeric'], 'positions.*.p3y' => ['required', 'numeric'], 'positions.*.p4x' => ['required', 'numeric'], 'positions.*.p4y' => ['required', 'numeric']]);
        $templateIds = $request->input('template_ids');
        $positions = collect($request->input('positions'))->keyBy('name')->map(fn($p) => ['p1x' => $p['p1x'], 'p1y' => $p['p1y'], 'p2x' => $p['p2x'], 'p2y' => $p['p2y'], 'p3x' => $p['p3x'], 'p3y' => $p['p3y'], 'p4x' => $p['p4x'], 'p4y' => $p['p4y']])->toArray();
        $sides = array_keys($positions);
        $colors = collect($request->input('colors') ?? [])->filter(fn($color) => is_string($color) && trim($color) !== '')->unique()->values()->all();
        $hasColors = count($colors) > 0;
        $newColorsNormalized = collect($colors)->map(fn($color) => $this->normalizeHex($color))->all();
        $renderJobs = $hasColors ? [] : collect($templateIds)->map(fn($templateId) =>
        ['template_id' => (string)$templateId, 'hex' => null, 'model_only' => true])->all();
        $mergedPivotColors = [];
        if ($type === 'single') {
            $templateId = (string)$templateIds[0];
            $existingTemplate = $mockup->templates()->wherePivot('type', 'single')->where('templates.id', $templateId)->first();
            $existingPivot = $existingTemplate?->pivot;
            $previousPositions = $existingPivot?->positions ?? [];
            $positionsChanged = json_encode($previousPositions) !== json_encode($request->input('positions'));
            if ($hasColors) {
                $mergedPivotColors[$templateId] = $colors;
                $existingMediaHexes = $mockup->media()->where('collection_name', 'generated_mockups')->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [$templateId])->get()->map(fn($media) => $this->normalizeHex($media->getCustomProperty('hex') ?? ''))->filter()->unique()->values()->all();
                $removedHexes = array_diff($existingMediaHexes, $newColorsNormalized);
                foreach ($removedHexes as $hex) {
                    $mockup->media()->where('collection_name', 'generated_mockups')->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [$templateId])->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex'))) = ?", [$hex])->get()->each(fn($media) => $media->delete());
                }
                $hexesToRender = array_diff($newColorsNormalized, $existingMediaHexes);
                $hexesWithMedia = array_intersect($newColorsNormalized, $existingMediaHexes);
                $modelColorHex = $existingPivot?->model_color ? $this->normalizeHex($existingPivot->model_color) : null;
                if ($modelColorHex && !in_array($modelColorHex, $newColorsNormalized)) {
                    $mockup->templates()->updateExistingPivot($templateId, ['model_color' => null]);
                }
                if ($positionsChanged) {
                    foreach ($hexesWithMedia as $hex) {
                        $mockup->media()->where('collection_name', 'generated_mockups')->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [$templateId])->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex'))) = ?", [$hex])->get()->each(fn($media) => $media->delete());
                    }
                    foreach ($newColorsNormalized as $hex) {
                        $renderJobs[] = ['template_id' => $templateId, 'hex' => $hex];
                    }
                } else {
                    foreach ($hexesToRender as $hex) {
                        $renderJobs[] = ['template_id' => $templateId, 'hex' => $hex];
                    }
                }
            } else {
                $mergedPivotColors[$templateId] = [];
                if ($positionsChanged) {
                    $mockup->media()->where('collection_name', 'generated_mockups')->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [$templateId])->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.model_image')) = ?", ['1'])->get()->each(fn($media) => $media->delete());
                }
            }
            $allExistingTemplates = $mockup->templates()->get()->keyBy('id');
            $syncData = [];
            foreach ($allExistingTemplates as $existingId => $template) {
                if ((string)$existingId === $templateId) {
                    continue;
                }
                $syncData[(string)$existingId] = ['colors' => $template->pivot->colors, 'positions' => $template->pivot->positions, 'model_color' => $template->pivot->model_color, 'type' => $template->pivot->type];
            }
            $syncData[$templateId] = ['positions' => $request->input('positions'), 'type' => 'single', 'colors' => $hasColors ? $colors : [], 'model_color' => $hasColors ? ($colors[0] ?? null) : null];
            $mockup->templates()->sync($syncData);
            if ($hasColors) {
                $mockup->update(['colors' => $colors]);
            } elseif ($colorsProvided) {
                $mockup->update(['colors' => []]);
            }
        }
        else {
            $existingTemplates = $mockup->templates()->get()->keyBy('id');
            $previouslyAttachedIds = $existingTemplates->keys()->map(fn($id) => (string)$id)->toArray();
            $incomingIds = collect($templateIds)->map(fn($id) => (string)$id)->toArray();
            $removedTemplateIds = array_diff($previouslyAttachedIds, $incomingIds);
            $newTemplateIds = array_diff($incomingIds, $previouslyAttachedIds);
            $alreadyAttachedTemplateIds = array_intersect($incomingIds, $previouslyAttachedIds);
            $singleTemplateIds = $mockup->templates()->wherePivot('type', 'single')->pluck('templates.id')->map(fn($id) => (string)$id)->toArray();
            foreach ($removedTemplateIds as $removedTemplateId) {
                if (in_array((string)$removedTemplateId, $singleTemplateIds)) {
                    continue;
                }
                $mockup->media()->where('collection_name', 'generated_mockups')->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [(string)$removedTemplateId])->get()->each(fn($media) => $media->delete());
            }
            foreach ($alreadyAttachedTemplateIds as $templateId) {
                $existingTemplate = $existingTemplates->get($templateId);
                $pivot = $existingTemplate->pivot;
                $previousPositions = $pivot->positions ?? [];
                $positionsChanged = json_encode($previousPositions) !== json_encode($request->input('positions'));
                if ($hasColors) {
                    $mergedPivotColors[$templateId] = $colors;
                    $mergedNormalizedHexes = $newColorsNormalized;
                    $existingMediaHexes = $mockup->media()->where('collection_name', 'generated_mockups')->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [(string)$templateId])->get()->map(fn($media) => $this->normalizeHex($media->getCustomProperty('hex') ?? ''))->filter()->unique()->values()->all();
                    $removedHexes = array_diff($existingMediaHexes, $mergedNormalizedHexes);
                    foreach ($removedHexes as $hex) {
                        $mockup->media()->where('collection_name', 'generated_mockups')->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [(string)$templateId])->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex'))) = ?", [$hex])->get()->each(fn($media) => $media->delete());
                    }
                    $hexesToRender = array_diff($mergedNormalizedHexes, $existingMediaHexes);
                    $hexesWithMedia = array_intersect($mergedNormalizedHexes, $existingMediaHexes);
                    $modelColorHex = $pivot?->model_color ? $this->normalizeHex($pivot->model_color) : null;
                    if ($modelColorHex && !in_array($modelColorHex, $mergedNormalizedHexes)) {
                        $mockup->templates()->updateExistingPivot($templateId, ['model_color' => null]);
                    }
                    if ($positionsChanged) {
                        foreach ($hexesWithMedia as $hex) {
                            $mockup->media()->where('collection_name', 'generated_mockups')->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [(string)$templateId])->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex'))) = ?", [$hex])->get()->each(fn($media) => $media->delete());
                        }
                        foreach ($mergedNormalizedHexes as $hex) {
                            $renderJobs[] = ['template_id' => $templateId, 'hex' => $hex];
                        }
                    } else {
                        foreach ($hexesToRender as $hex) {
                            $renderJobs[] = ['template_id' => $templateId, 'hex' => $hex];
                        }
                    }
                } else {
                    $mergedPivotColors[$templateId] = [];
                    if ($positionsChanged) {
                        $mockup->media()->where('collection_name', 'generated_mockups')->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [(string)$templateId])->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.model_image')) = ?", ['1'])->get()->each(fn($media) => $media->delete());
                    }
                }
            }
            foreach ($newTemplateIds as $templateId) {
                if ($hasColors) {
                    $mergedPivotColors[$templateId] = $colors;
                    foreach ($newColorsNormalized as $hex) {
                        $renderJobs[] = ['template_id' => $templateId, 'hex' => $hex];
                    }
                } else {
                    $mergedPivotColors[$templateId] = [];
                }
            }
            $singleTemplates = $mockup->templates()->wherePivot('type', 'single')->get()->keyBy('id');
            $syncData = [];
            foreach ($singleTemplates as $templateId => $template) {
                $syncData[(string)$templateId] = ['colors' => $template->pivot->colors, 'positions' => $template->pivot->positions, 'model_color' => $template->pivot->model_color, 'type' => 'single'];
            }
            foreach ($templateIds as $templateId) {
                $pivotColors = $hasColors ? ($mergedPivotColors[$templateId] ?? $colors) : [];
                $syncData[(string)$templateId] = ['positions' => $request->input('positions'), 'type' => 'bulk', 'colors' => $pivotColors, 'model_color' => $pivotColors[0] ?? null];
            }
            $mockup->templates()->sync($syncData);
            if ($hasColors) {
                $mockup->update(['colors' => $colors]);
            } elseif ($colorsProvided) {
                $mockup->update(['colors' => []]);
            }
        }
        $hexToOriginalColor = collect($colors)->keyBy(fn($color) => $this->normalizeHex($color))->all();
        $totalCount = count($renderJobs) * count($sides);
        $bulkJob = MockupGenerationJob::create(['mockup_id' => $mockup->id, 'status' => 'pending', 'total_count' => $totalCount, 'completed_count' => 0, 'failed_count' => 0]);
        foreach ($renderJobs as $job) {
            $isModelOnly = $job['model_only'] ?? false;
            $originalColor = $isModelOnly ? null : ($hexToOriginalColor[$job['hex']] ?? $job['hex']);

            foreach ($sides as $side) {
                BulkJobItem::create([
                    'bulk_job_id' => $bulkJob->id,
                    'template_id' => $job['template_id'],
                    'color' => $originalColor,
                    'side' => $side,
                    'points' => $positions[$side],
                    'status' => 'pending',
                ]);
            }
        }

        $bulkJob->update([
            'status' => $totalCount > 0 ? 'processing' : 'completed',
            'started_at' => now(),
            'completed_at' => $totalCount > 0 ? null : now(),
        ]);

        if ($totalCount > 0) {
            $this->dispatchNextRenderItem($bulkJob, $mockup);
        }
        return Response::api(data: ['success' => true, 'bulk_job_id' => $bulkJob->id, 'total_count' => $totalCount, 'sides' => $sides, 'colors' => $hasColors ? $colors : [], 'rendered_jobs' => count($renderJobs)]);
    }
    public function status($id)
    {
        $job = MockupGenerationJob::findOrFail($id);
        $elapsed = now()->diffInSeconds($job->started_at);
        $rate = $job->completed_count / max($elapsed, 1);
        $remaining = ($job->total_count - $job->completed_count) / max($rate, 0.01);

        return Response::api(data: [
            'id' => $job->id,
            'status' => $job->status,
            'total_count' => $job->total_count,
            'completed_count' => $job->completed_count,
            'failed_count' => $job->failed_count,
            'percent' => round(($job->completed_count / max($job->total_count, 1)) * 100, 1),
            'estimated_remaining_seconds' => (int)$remaining,
            'started_at' => $job->started_at,
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
            $hex = $this->normalizeHex($item->color);

            $mockup->media()
                ->where('collection_name', 'generated_mockups')
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [(string)$item->template_id])
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
                'status' => 'pending',
                'error_message' => null,
                'output_path' => null,
            ]);

        $bulkJob->update([
            'status' => 'processing',
            'failed_count' => 0,
            'total_count' => $bulkJob->completed_count + $failedItems->count(),
            'completed_at' => null,
            'started_at' => now(),
        ]);

        $this->dispatchNextRenderItem($bulkJob->fresh(), $mockup);

        return Response::api(data: [
            'success' => true,
            'retried' => $failedItems->count(),
            'bulk_job_id' => $bulkJob->id,
        ]);
    }

    /**
     * Claim and dispatch only one pending item at a time.
     */
    private function dispatchNextRenderItem(
        MockupGenerationJob $bulkJob,
        Mockup $mockup
    ): void {
        $item = DB::transaction(function () use ($bulkJob) {
            $job = MockupGenerationJob::query()
                ->lockForUpdate()
                ->find($bulkJob->id);

            if (
                !$job ||
                in_array(
                    $job->status,
                    ['completed', 'completed_with_errors', 'failed', 'cancelled'],
                    true
                )
            ) {
                return null;
            }

            $hasActiveItem = BulkJobItem::query()
                ->where('bulk_job_id', $job->id)
                ->where('status', 'processing')
                ->exists();

            if ($hasActiveItem) {
                return null;
            }

            $nextItem = BulkJobItem::query()
                ->where('bulk_job_id', $job->id)
                ->where('status', 'pending')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (!$nextItem) {
                return null;
            }

            $nextItem->update([
                'status' => 'processing',
            ]);

            return $nextItem->fresh();
        });

        if (!$item) {
            return;
        }

        RenderMockupJob::dispatch(
            $bulkJob->fresh(),
            $item,
            $mockup->fresh()
        );
    }

    private function normalizeHex(string $color): string
    {
        return strtolower(ltrim(trim($color), '#'));
    }
}
