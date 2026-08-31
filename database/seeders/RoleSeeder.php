<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::where('name', 'reports.reply')->delete();

        foreach (User::PERMISSION_CATALOG as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => $module . '.' . $action,
                    'guard_name' => 'web',
                ]);
            }
        }

        foreach (User::PERMISSIONS as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web'], ['role' => $roleName]);
            $permissionModels = collect($permissions)->map(fn ($permissionName) =>
                Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web'])
            );

            $role->update(['role' => $roleName]);
            $role->syncPermissions($permissionModels);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
