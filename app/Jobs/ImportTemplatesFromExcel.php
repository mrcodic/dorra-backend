<?php

namespace App\Jobs;

use App\Services\TemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportTemplatesFromExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $excelRel,
        public string $zipRel,
        public string $batch
    ) {}

    public function handle(TemplateService $service): void
    {
        $excelAbs = Storage::disk('local')->path($this->excelRel);
        $zipAbs   = Storage::disk('local')->path($this->zipRel);

        if (!file_exists($excelAbs) || !file_exists($zipAbs)) {
            Log::error('Import files missing', [
                'batch' => $this->batch,
                'excelRel' => $this->excelRel,
                'zipRel' => $this->zipRel,
                'excelAbs' => $excelAbs,
                'zipAbs' => $zipAbs,
                'exists_excel' => file_exists($excelAbs),
                'exists_zip'   => file_exists($zipAbs),
            ]);
            return;
        }

        $result = $service->importExcelFromPaths($excelAbs, $zipAbs, $this->batch);

        Log::info('Import finished', [
            'batch' => $this->batch,
            'created' => $result['created'] ?? null,
            'skipped_count' => $result['skipped_count'] ?? null,
        ]);
    }
}
