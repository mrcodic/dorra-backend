<?php

namespace App\Console\Commands;

use App\Models\Template;
use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SyncCanvasAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-canvas-assets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $template = Template::query()
            ->find('a0dd40d8-84cc-462d-b253-da602863bc71');
//            ->where(function ($q) {
//                $q->whereNotNull('design_data')
//                    ->orWhereNotNull('design_back_data');
//            })
//            ->chunkById(200, function ($templates) {

//                foreach ($templates as $template) {

                    $this->processCanvasColumn($template, 'design_data');
                    $this->processCanvasColumn($template, 'design_back_data');

//                }
//            });
    }
    public function processCanvasColumn(Template $template, string $column): void
    {
        if (!$template->$column) {
            return;
        }
        $json = json_decode($template->$column, true);

        if (!$json) {
            return;
        }

        $found = [];

        $this->walkFabric($json, function (&$obj) use (&$found) {
            if (($obj['type'] ?? null) === 'image' && !empty($obj['src'])) {
                $found[] =& $obj;
            }
        });

        $changed = false;
        dd($json['objects']);
        foreach ($json['objects'] as &$object) {
            if ($object['type'] === 'image' && isset($object['src'])) {

                dd($object['src']);
            }
        }
        foreach ($found as &$imgObj) {

            [$fullSrc, $path] = $this->normalizeSrc($imgObj['src']);

            $media = Media::query()
                ->where('file_name', basename($path))
                ->latest()
                ->first();

            if (!$media) {
                continue;
            }

            $newMedia = $media->copy($template, 'template-library-assets');

            $template->libraryMedia()->syncWithoutDetaching([$newMedia->id]);

            if (empty($imgObj['assetId'])) {
                $imgObj['assetId'] = $newMedia->id;
                $changed = true;
            }
        }

        if ($changed) {
            $template->$column = json_encode($json, JSON_UNESCAPED_UNICODE);
            $template->save();
        }
    }
    private function normalizeSrc(string $src): array
    {
        $src = trim($src);
        $path = parse_url($src, PHP_URL_PATH) ?: $src;
        $path = preg_replace('#/+#', '/', $path);
        return [$src, $path];
    }

    private function walkFabric($node, callable $fn): void
    {
        if (is_array($node)) {
            $fn($node);

            if (isset($node['objects']) && is_array($node['objects'])) {
                foreach ($node['objects'] as $child) $this->walkFabric($child, $fn);
            }

            if (isset($node['clipPath']) && is_array($node['clipPath'])) {
                $this->walkFabric($node['clipPath'], $fn);
            }
        }
    }
}
