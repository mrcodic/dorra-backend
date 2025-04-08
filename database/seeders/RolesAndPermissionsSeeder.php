<?php

namespace Database\Seeders;

use App\Enums\Admin\PermissionEnum;
use App\Enums\Admin\RoleEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (PermissionEnum::cases() as $permissionEnum) {
            Permission::query()->firstOrCreate([
                'name' => $permissionEnum->value,
                'guard_name' => 'web',
                'routes' => json_encode($permissionEnum->routes()),
            ]);
        }

        foreach (RoleEnum::cases() as $roleEnum) {
           $role = Role::query()->firstOrCreate([
                'name' => $roleEnum->value,
               'guard_name' => 'web',
            ]);
           $role->syncPermissions($roleEnum->permissions());
        }
    }
}
