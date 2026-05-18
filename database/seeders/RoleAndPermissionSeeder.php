<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permManageProducts = Permission::firstOrCreate(['name' => 'manage products', 'guard_name' => 'web']);
        $permManageTransactions = Permission::firstOrCreate(['name' => 'manage transactions', 'guard_name' => 'web']);
        $permViewFinance = Permission::firstOrCreate(['name' => 'view finance', 'guard_name' => 'web']);
        $permViewStocks = Permission::firstOrCreate(['name' => 'view stocks', 'guard_name' => 'web']);

        // Create roles and assign created permissions
        $roleOperator = Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);
        $roleOperator->givePermissionTo([$permManageTransactions, $permViewStocks]);

        $roleFinance = Role::firstOrCreate(['name' => 'finance', 'guard_name' => 'web']);
        $roleFinance->givePermissionTo([$permViewFinance, $permViewStocks]);

        $roleAdmin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $roleAdmin->givePermissionTo([$permManageProducts, $permManageTransactions, $permViewFinance, $permViewStocks]);
    }
}
