<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class VoidPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'VOID INDEX FA VOUCHER',
            'VOID INDEX INVOICE',
        ];

        foreach ($permissions as $permissionName) {
            Permission::updateOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web']
            );
        }

        // Assign to Super Admin role
        $role = Role::where('name', 'Super Admin')->first();
        if ($role) {
            $role->givePermissionTo($permissions);
        }
    }
}
