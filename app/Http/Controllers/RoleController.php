<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\RedirectResponse;

class RoleController extends Controller
{
    public function index(): Response
    {
        // Ambil semua role beserta permission-nya
        $roles = Role::with('permissions')->get()->map(function ($role) {
            return [
                'id'          => $role->id,
                'name'        => $role->name,
                'permissions' => $role->permissions->pluck('name')->toArray(),
            ];
        });

      
        $permissions = Permission::all()->pluck('name')->toArray();

        return Inertia::render('Roles/Index', [
            'roles'       => $roles,
            'permissions' => $permissions,
        ]);
    }
    public function toggle(Request $request, Role $role): RedirectResponse
    {
        $request->validate([
            'permission' => 'required|string|exists:permissions,name',
            'is_enabled' => 'required|boolean',
        ]);

        if ($role->name === 'admin') {
            return back()->with('error', 'Hak akses Admin bersifat absolut dan tidak dapat diubah.');
        }

        // Toggle dari Spatie
        if ($request->is_enabled) {
            $role->givePermissionTo($request->permission);
        } else {
            $role->revokePermissionTo($request->permission);
        }

        return back()->with('success', "Hak akses '{$request->permission}' berhasil diperbarui untuk role {$role->name}.");
    }
}