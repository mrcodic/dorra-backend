<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Controllers\Controller;
use App\Models\ProductSpecification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;


class FixedSpecController extends Controller
{

    public function destroy(ProductSpecification $productSpecification)
    {
        DB::transaction(function () use ($productSpecification) {

            $specifiable = $productSpecification->specifiable()->with([
                'specifications.options',
                'variants',
            ])->first();

            $productSpecification->delete();

            $specifiable->load('specifications.options');

            $specs = $specifiable->specifications
                ->map(function ($spec) {
                    return $spec->options
                        ->map(fn($opt) => trim((string) $opt->getTranslation('value', 'en')))
                        ->filter()
                        ->values()
                        ->all();
                })
                ->filter(fn($opts) => count($opts))
                ->values()
                ->all();

            $allowedCodes = $this->cartesianCodes($specs);
            $specifiable->variants()
                ->whereNotIn('key', $allowedCodes)
                ->delete();
        });

        return Response::api();
    }

    /**
     * Build codes exactly like your JS:
     * value.replace(/\s+/g,'').toLowerCase() joined with "_"
     */
    private function cartesianCodes(array $specOptions): array
    {
        if (empty($specOptions)) return [];

        $result = [[]];

        foreach ($specOptions as $opts) {
            $tmp = [];
            foreach ($result as $prefix) {
                foreach ($opts as $v) {
                    $tmp[] = array_merge($prefix, [$v]);
                }
            }
            $result = $tmp;
        }

        return array_map(function ($combo) {
            $parts = array_map(function ($v) {
                $v = preg_replace('/\s+/', '', mb_strtolower((string) $v));
                return $v;
            }, $combo);

            return implode('_', $parts);
        }, $result);
    }
}
