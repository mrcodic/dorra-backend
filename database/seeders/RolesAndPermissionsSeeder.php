<?php

namespace Database\Seeders;

use App\Enums\Admin\PermissionEnum;
use App\Enums\Admin\RoleEnum;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (PermissionEnum::cases() as $permissionEnum) {
            Permission::query()->firstOrCreate(
                ['name' => $permissionEnum->value],
            [
                'group_key' => $permissionEnum->group()['key'],
                'group' => $permissionEnum->group()['value'],
                'guard_name' => 'web',
                'routes' => $permissionEnum->routes(),
            ]);
        }

//        foreach (RoleEnum::cases() as $roleEnum) {
//           $role = Role::query()->firstOrCreate([
//                'name' => $roleEnum->value,
//               'guard_name' => 'web',
//            ]);
//           $role->syncPermissions($roleEnum->permissions());
//        }
    }
}
