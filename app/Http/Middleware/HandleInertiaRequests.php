<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Jembatan penghubung: Tarik permission berdasarkan string 'role' user yang login
        $permissions = [];
        
        if ($request->user()) {
            // Cari role di Spatie yang namanya sama dengan string role di tabel users
            $role = \Spatie\Permission\Models\Role::with('permissions')
                ->where('name', $request->user()->role)
                ->first();
                
            if ($role) {
                $permissions = $role->permissions->pluck('name')->toArray();
            }
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
                // Lempar array permissions yang sudah fix ini ke Vue
                'permissions' => $permissions,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ]);
    }

    
}
