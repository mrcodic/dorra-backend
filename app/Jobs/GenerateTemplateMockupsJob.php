<?php

namespace App\Jobs;

use App\Models\Template;
use App\Observers\MockupObserver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateTemplateMockupsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $templateId,
        public bool $force = false
    ) {
    }

    public function handle(
        MockupObserver $observer
    ): void {
        $template = Template::query()
            ->find($this->templateId);

        if (!$template) {
            return;
        }

        $template
            ->mockups()
            ->select('mockups.*')
            ->lazyById(
                100,
                'mockups.id',
                'id'
            )
            ->each(function ($mockup) use (
                $template,
                $observer
            ) {
                $observer->syncTemplateForMockup(
                    $mockup,
                    $template
                );

                $observer->generateTemplateFiles(
                    $mockup,
                    $template,
                    $this->force
                );
            });
    }
}
