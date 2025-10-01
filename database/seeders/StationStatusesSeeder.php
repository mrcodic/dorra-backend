<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Station;
use App\Models\StationStatus;
use App\Enums\JobTicket\StatusEnum;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class StationStatusesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();


        $map = [
            'prepress' => [
                StatusEnum::PREPRESS_QUEUE,
                StatusEnum::PREPRESS_IN_PROGRESS,
                StatusEnum::PREPRESS_DONE,
            ],
            'print' => [
                StatusEnum::PRINT_QUEUE,
                StatusEnum::PRINTING,
                StatusEnum::PRINTED,
            ],
            'finish' => [
                StatusEnum::FINISH_QUEUE,
                StatusEnum::FINISHING,
                StatusEnum::FINISHED,
            ],
            'qc' => [
                StatusEnum::QC_QUEUE,
                StatusEnum::QC_PASSED,
                StatusEnum::QC_FAILED,
            ],
            'pack' => [
                StatusEnum::PACK_QUEUE,
                StatusEnum::PACKED,
                StatusEnum::SHIPPED,
            ],
        ];

        DB::transaction(function () use ($map, $now) {

            $stations = Station::whereIn('code', array_keys($map))
                ->get()
                ->keyBy('code');

            collect($map)->each(function (array $enumCases, string $stationCode) use ($stations, $now) {
                $station = $stations->get($stationCode);

                if (!$station) {
                    $this->command?->warn("Station '{$stationCode}' not found. Skipping.");
                    return;
                }


                $rows = collect($enumCases)
                    ->values()
                    ->map(function (StatusEnum $enum, int $i) use ($enumCases, $station, $now) {
                        return [
                            'station_id'  => $station->id,
                            'code'        => $this->enumCode($enum),
                            'name'        => $enum->label(),
                            'sequence'    => $i + 1,
                            'is_terminal' => $i === array_key_last($enumCases),
                            'created_at'  => $now,
                            'updated_at'  => $now,
                        ];
                    })
                    ->all();

                StationStatus::upsert(
                    $rows,
                    ['station_id', 'code'],
                    ['name', 'sequence', 'is_terminal', 'updated_at']
                );

                $this->command?->info("Seeded statuses for station: {$stationCode}");
            });
        });
    }

    protected function enumCode(StatusEnum $enum): string
    {
        return Str::of($enum->name)->lower()->replace('_', ' ')->slug('_');
    }
}
