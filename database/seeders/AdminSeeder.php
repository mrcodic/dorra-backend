<?php

namespace Database\Seeders;

use App\Enums\Admin\PermissionEnum;
use App\Models\Admin;
use App\Models\Role;
use Illuminate\Database\Seeder;


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
        $role = Role::firstOrCreate(
            [
                'name' => json_encode([
                    'en' => 'Super Admin',
                    'ar' => 'مشرف عام',
                ]),
                'guard_name' => 'web',
            ]
        );

        $admin->roles()->sync([
            $role->id => ['model_type' => $admin->getMorphClass()]
        ]);


        $role->syncPermissions(PermissionEnum::values());
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
