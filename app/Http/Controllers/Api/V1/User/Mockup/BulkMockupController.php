<?php

namespace App\Http\Controllers\Api\V1\User\Mockup;

use App\Http\Controllers\Controller;
use App\Jobs\RenderMockupJob;
use App\Models\BulkJobItem;
use App\Models\Mockup;
use App\Models\MockupGenerationJob;
use App\Models\Template;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            ->map(fn($c) => $this->normalizeHex($c))
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
                'colors'    => json_encode($colors),
                'positions' => json_encode($positions),
            ];
        }
        $mockup->templates()->syncWithoutDetaching($syncData);

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
                        'points'      => json_encode($positions[$side]),
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

        return response()->json([
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

        return response()->json([
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

        return response()->json([
            'success' => true,
            'message' => "Cancelled. {$job->completed_count} images kept.",
        ]);
    }

    private function normalizeHex(string $hex): string
    {
        return strtolower(ltrim(trim($hex), '#'));
    }
}
