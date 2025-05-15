<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
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
