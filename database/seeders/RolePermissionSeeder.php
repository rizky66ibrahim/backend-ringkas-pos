<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{

    private $permissions = [
        'role-list',
        'role-create',
        'role-edit',
        'role-delete',
        'user-list',
        'user-create',
        'user-edit',
        'user-delete',
        'product-list',
        'product-create',
        'product-edit',
        'product-delete',
        'category-list',
        'category-create',
        'category-edit',
        'category-delete',
        'unit-list',
        'unit-create',
        'unit-edit',
        'unit-delete',
        'warehouse-list',
        'warehouse-create',
        'warehouse-edit',
        'warehouse-delete',
        'access-dashboard',
    ];


    public function run(): void
    {
        foreach ($this->permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create Role : Admin, Kasir dan User
        $roleAdmin = Role::create(['name' => 'Admin']);
        $roleKasir = Role::create(['name' => 'Kasir']);
        $roleUser = Role::create(['name' => 'User']);

        // Assign Permission to Role
        $roleAdmin->givePermissionTo($this->permissions);
        $roleKasir->givePermissionTo([
            'product-list',
            'product-create',
            'product-edit',
            'product-delete',
            'category-list',
            'category-create',
            'category-edit',
            'category-delete',
            'unit-list',
            'unit-create',
            'unit-edit',
            'unit-delete',
            'warehouse-list',
            'warehouse-create',
            'warehouse-edit',
            'warehouse-delete',
            'access-dashboard',
        ]);

        $roleUser->givePermissionTo([
            'product-list',
            'category-list',
            'unit-list',
            'warehouse-list',
        ]);

        // Find the user with the name "Admin"
        $adminUser = User::where('name', 'Admin')->first();
        $kasirUser = User::where('name', 'Kasir')->first();
        $userUser = User::where('name', 'User')->first();

        // Assign Role to User
        $adminUser->assignRole($roleAdmin);
        $kasirUser->assignRole($roleKasir);
        $userUser->assignRole($roleUser);
    }
}
