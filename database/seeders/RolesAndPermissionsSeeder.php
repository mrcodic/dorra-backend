<?php

namespace Database\Seeders;

use App\Enums\Admin\PermissionEnum;
use App\Enums\Admin\RoleEnum;
use App\Models\Permission; // your custom Permission model (casts on group/routes)
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionEnum::cases() as $perm) {
            $name     = $perm->value;
            $prefix   = Str::before($name, '_');
            $groupArr = $perm->group();
            $routes   = $perm->routes();


            $groupKey = ($groupArr['key'] ?? $prefix) ?: $prefix;


            Permission::updateOrCreate(

                ['name' => $name, 'guard_name' => 'web'],

                [
                    'group_key'  => $groupKey,

                    'group'      => ['en' => $groupArr['value'] ?? Str::headline(str_replace('-', ' ', $groupKey))],
                    'routes'     => array_values($routes ?? []),
                ]
            );
        }

        // If you want to seed roles and sync permissions, uncomment:
        /*
        foreach (RoleEnum::cases() as $roleEnum) {
            $role = Role::query()->updateOrCreate(
                ['name' => $roleEnum->value, 'guard_name' => 'web'],
                []
            );
            $role->syncPermissions($roleEnum->permissions());
        }
        */


        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
