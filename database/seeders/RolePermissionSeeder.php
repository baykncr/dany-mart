<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Buat daftar Permission (Fitur)
        $permissions = [
            'manage-products',
            'manage-categories',
            'manage-users',
            'manage-roles',
            'view-reports',
            'access-pos',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Buat Role 'admin' dan beri SEMUA permission
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleAdmin->givePermissionTo(Permission::all());

        // 3. Buat Role 'user' (Kasir) dan beri permission terbatas (bisa diubah nanti di UI)
        $roleKasir = Role::firstOrCreate(['name' => 'user']);
        $roleKasir->givePermissionTo(['access-pos']); 

        // 4. Assign role 'admin' ke user yang sudah ada (Misal user pertama di database)
        $firstUser = User::first();
        if ($firstUser) {
            // Kita pastikan user pertama selalu jadi admin
            $firstUser->assignRole('admin'); 
        }
    }
}