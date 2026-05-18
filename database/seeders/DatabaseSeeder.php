<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Roles and Permissions
        $this->call(RoleAndPermissionSeeder::class);

        // 2. Seed Products and Stocks
        $this->call(ProductSeeder::class);

        // 3. Create Admin User
        $admin = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => \Illuminate\Support\Facades\Hash::make('admin12'),
        ]);
        $admin->assignRole('admin');

        // 4. Create Operator User
        $operator = User::factory()->create([
            'name' => 'Operator Gudang',
            'email' => 'operator@smartinventory.com',
        ]);
        $operator->assignRole('operator');
    }
}
