<?php

namespace App\Jobs;

use App\Models\Design;
use App\Models\DesignVersion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CopyDesignMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Design $design;
    public DesignVersion $designVersion;

    public function __construct(Design $design, DesignVersion $designVersion)
    {
        $this->design = $design;
        $this->designVersion = $designVersion;
    }

    public function handle(): void
    {
        $firstMedia = $this->design->getFirstMedia('designs');
        $backFirstMedia = $this->design->getFirstMedia('back_designs');
        if ($firstMedia) {
            $firstMedia->copy($this->designVersion, 'design-versions');
        }
        if ($backFirstMedia) {
            $backFirstMedia->copy($this->designVersion, 'back-design-versions');
        }
    }
}
