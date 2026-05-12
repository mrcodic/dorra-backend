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
            ->map(fn($c) => $c)
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

        // -----------------------------------------------------------------------
        // Sync each template: update colors + positions on the pivot
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
        // Dispatch bulk job
        // -----------------------------------------------------------------------
        $totalCount = count($templateIds) * count($colors) * count($sides);

        $bulkJob = MockupGenerationJob::create([
            'mockup_id'       => $mockup->id,
            'status'          => 'pending',
            'total_count'     => $totalCount,
            'completed_count' => 0,
            'failed_count'    => 0,
        ]);

        foreach ($templateIds as $templateId) {
            foreach ($colors as $color) {
                foreach ($sides as $side) {
                    $item = BulkJobItem::create([
                        'bulk_job_id' => $bulkJob->id,
                        'template_id' => $templateId,
                        'color'       => $color,
                        'side'        => $side,
                        'points'      => $positions[$side],
                        'status'      => 'pending',
                    ]);

                    RenderMockupJob::dispatch($bulkJob, $item, $mockup);
                }
            }
        }

        $bulkJob->update([
            'status'     => 'processing',
            'started_at' => now(),
        ]);

        return Response::api(data:[
            'success'     => true,
            'bulk_job_id' => $bulkJob->id,
            'total_count' => $totalCount,
            'sides'       => $sides,
            'colors'      => $colors,
        ]);
    }

    public function status($id)
    {
        $job     = MockupGenerationJob::findOrFail($id);
        $elapsed = now()->diffInSeconds($job->started_at);
        $rate    = $job->completed_count / max($elapsed, 1);
        $remaining = ($job->total_count - $job->completed_count) / max($rate, 0.01);

        return Response::api(data:[
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

        // ✅ Dispatch only after all rows are already 'pending' in the DB
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
