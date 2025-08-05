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
    public function get(?string $key, $default = null)
    {
        $settings = Cache::rememberForever('app_settings', function () {
            return Setting::pluck('value', 'key')->toArray();
        });
        return $key ? $settings[$key] : $settings;
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
