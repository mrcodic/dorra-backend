<?php

namespace Database\Seeders;

use App\Enums\Admin\PermissionEnum;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      $admin =  Admin::query()->firstOrCreate([ 'email' => 'super@admin.com'],[
            'first_name' =>'super',
            'last_name' => 'admin',
            'email' => 'super@admin.com',
            'phone_number' => '+201503464414',
            'password' => 123456789,
            'status' => 1,
        ]);
        $role = Role::query()->firstOrCreate([
                'name' => 'super admin',
               'guard_name' => 'web',
            ]);
        $role->syncPermissions(PermissionEnum::values());
        $admin->assignRole($role);
    }
}
