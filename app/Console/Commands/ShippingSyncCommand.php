<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\ShippingLocationMapping;
use App\Models\State;
use App\Models\Zone;
use App\Services\Shipping\Contracts\LocationsProvider;
use App\Services\Shipping\ShippingManger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ShippingSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipping:sync {driver=shipblu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(ShippingManger $shippingManger)
    {
        $driverName = $this->argument('driver');
        $driver = $shippingManger->driver($driverName);
        if (!$driver instanceof LocationsProvider) {
            $this->error("Driver [$driverName] does not implement LocationsProvider.");
            return self::FAILURE;
        }

        $governorates = $driver->governorates();
        foreach ($governorates as $governorate) {
            DB::transaction(function () use ($governorate, $driverName, $driver) {
                $gov = $this->upsertGovernorate($driverName, $governorate);
                $cities = $driver->cities($governorate['id']);
                foreach ($cities as $city) {
                    $c = $this->upsertCity($driverName, $gov->id, $city);
                    $zones = $driver->zones($city['id']);
                    foreach ($zones as $zone) {
                        $this->upsertZone($driverName, $c->id, $zone);

                    }

                }

            });

        }
        $this->info('Shipping sync complete.');
        return self::SUCCESS;
    }

    private function upsertGovernorate(string $provider, array $governorate)
    {
        $map = ShippingLocationMapping::where([
            'provider' => $provider, 'external_id' => (string)$governorate['id'],
        ])->where('locatable_type', Country::class)->first();
        if (!$map) {
            $country = Country::create([
                'name' => [
                    'en' => $governorate['name'],
                    'ar' => $governorate['name'],
                ],
                'code' => $governorate['code'],
            ]);
            ShippingLocationMapping::create([
                'provider' => $provider,
                'external_id' => (string)$governorate['id'],
                'locatable_type' => Country::class,
                'locatable_id' => $country->id,
            ]);
            return $country;
        }
        $country = Country::findOrFail($map->locatable_id);
        $country->update([
            'name' => [
                'en' => $governorate['name'],
                'ar' => $governorate['name'],
            ],
            'code' => $governorate['code'],
        ]);
        return $country;
    }

    private function upsertCity(string $provider, int $governorateId, array $city)
    {
        $map = ShippingLocationMapping::where([
            'provider' => $provider, 'external_id' => (string)$city['id'],
        ])->where('locatable_type', State::class)->first();
        if (!$map) {
            $state = State::create([
                'name' => [
                    'en' => $city['name'],
                    'ar' => $city['name'],
                ],
                'code' => $city['governorate']['code'],
                'country_id' => $governorateId,
            ]);
            ShippingLocationMapping::create([
                'provider' => $provider,
                'external_id' => (string)$city['id'],
                'locatable_type' => State::class,
                'locatable_id' => $state->id,
            ]);
            return $state;
        }
         $state = State::findOrFail($map->locatable_id);
        $state->update([
            'name' => [
                'en' => $city['name'],
                'ar' => $city['name'],
            ],
            'code' => $city['governorate']['code'],
            'country_id' => $governorateId,
        ]);
        return $state;
    }

    private function upsertZone(string $provider, int $cityId, array $zone)
    {
        $map = ShippingLocationMapping::where([
            'provider' => $provider, 'external_id' => (string)$zone['id'],
        ])->where('locatable_type', Zone::class)->first();
        if (!$map) {
            $z = Zone::create([
                'name' => [
                    'en' => $zone['name'],
                    'ar' => $zone['name'],
                ],
                'code' => $zone['city']['governorate']['code'],
                'state_id' => $cityId,
            ]);
            ShippingLocationMapping::create([
                'provider' => $provider,
                'external_id' => (string)$zone['id'],
                'locatable_type' => Zone::class,
                'locatable_id' => $z->id,
            ]);
            return $z;
        }
        $z = Zone::findOrFail($map->locatable_id);
        $z->update([
            'name' => [
                'en' => $zone['name'],
                'ar' => $zone['name'],
            ],
            'code' => $zone['city']['governorate']['code'],
            'state_id' => $cityId,
        ]);
        return $z;
    }


}
