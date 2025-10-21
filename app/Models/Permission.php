<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Translatable\HasTranslations;

class Permission extends SpatiePermission
{
    use HasTranslations;
    protected $fillable =[
        'name',
        'guard_name',
        'group',
        'group_key',
        'routes'
        ];
    public $translatable = ['group'];

    protected $casts = [
        'routes' => 'array',
    ];

        public function getRoutesByActionAndGroup($action, $group): array
    {
        return match ($action) {
            "create" => ["$group.create", "$group.store"],
            "read" => ["$group.show"],
            "update" => ["$group.edit", "$group.update"],
            "delete" => ["$group.destroy"],
        };
    }
}
