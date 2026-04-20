<?php
// database/seeders/RolesAndPermissionsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage employees',
            'view employees',
            'manage attendance',
            'view attendance',
            'manage locations',
            'view locations',
            'manage gps tracking',
            'view gps tracking',
            'manage reports',
            'view reports',
            'manage roles',
            'manage own attendance',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $superAdminRole = Role::create(['name' => 'super-admin']);
        $adminRole = Role::create(['name' => 'admin']);
        $managerRole = Role::create(['name' => 'manager']);
        $employeeRole = Role::create(['name' => 'employee']);

        // Assign permissions to roles
        $superAdminRole->givePermissionTo(Permission::all());
        
        $adminRole->givePermissionTo([
            'manage employees', 'view employees',
            'manage attendance', 'view attendance',
            'manage locations', 'view locations',
            'manage gps tracking', 'view gps tracking',
            'manage reports', 'view reports'
        ]);
        
        $managerRole->givePermissionTo([
            'view employees', 'view attendance', 'view gps tracking', 'view reports'
        ]);
        
        $employeeRole->givePermissionTo(['manage own attendance']);

        // Create super admin user
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
            'phone' => '1234567890',
        ]);
        $superAdmin->assignRole($superAdminRole);

        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'phone' => '1234567891',
        ]);
        $admin->assignRole($adminRole);

        // Create manager user
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'phone' => '1234567892',
        ]);
        $manager->assignRole($managerRole);

        // Create employee user
        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
            'phone' => '1234567893',
            'manager_id' => $manager->id,
        ]);
        $employee->assignRole($employeeRole);

        // Create default location
        $location = \App\Models\Location::create([
            'name' => 'Main Office',
            'latitude' => 21.001517,
            'longitude' => 75.5778081,
            'radius' => 100,
            'address' => 'Main Office Address',
            'is_active' => true,
        ]);

        // Assign location to employee
        //$employee->locations()->attach($location->id);
    }
}