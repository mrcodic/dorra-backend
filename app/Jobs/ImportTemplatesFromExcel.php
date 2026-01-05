<?php

namespace App\Jobs;

use App\Services\TemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\UploadedFile;

class ImportTemplatesFromExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $excelPath;
    public string $zipPath;

    public function __construct(string $excelPath, string $zipPath)
    {
        $this->excelPath = $excelPath;
        $this->zipPath   = $zipPath;
    }

    public function handle(TemplateService $service)
    {
        $service->importExcel(
            new UploadedFile($this->excelPath, basename($this->excelPath)),
            new UploadedFile($this->zipPath, basename($this->zipPath))
        );
    }
}

