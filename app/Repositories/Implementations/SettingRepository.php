<?php

namespace App\Repositories\Implementations;

use App\Models\Setting;
use App\Repositories\{Base\BaseRepository, Interfaces\SettingRepositoryInterface,};
use Illuminate\Support\Facades\Cache;

class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    public function __construct(Setting $setting)
    {
        parent::__construct($setting);
    }
    public function get(?string $key = null, $default = null, ?string $group = null)
    {
        $settings = Cache::rememberForever('app_settings', function () {
            return Setting::query()->select('key', 'value', 'group')->get();
        });

        if ($group) {
            $settings = $settings->where('group', $group)->pluck('value', 'key')->toArray();
        } else {
            $settings = $settings->pluck('value', 'key')->toArray();
        }
        return $key ? ($settings[$key] ?? $default) : $settings;
    }


    public function update(array $data, $id)
    {
        Cache::forget('app_settings');
        return parent::update($data, $id);
    }

    /**
     * Clear settings cache (if updated).
     */
    public function clearCache()
    {
        Cache::forget('app_settings');
    }
}
