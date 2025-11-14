<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Creating roles and permissions...');

        // Create super_admin role if it doesn't exist
        // Note: With 'define_via_gate' => true in config, super_admin bypasses all permission checks
        if (! Role::where('name', 'super_admin')->exists()) {
            Role::create(['name' => 'super_admin']);
            $this->command->info('✅ super_admin role created (bypasses all permission checks via gate)');
        } else {
            $this->command->info('ℹ️  super_admin role already exists');
        }

        $this->command->info('✅ Roles and permissions setup completed');
    }
}
