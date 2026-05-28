<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::withCount(['orders', 'stockHistories'])
            ->orderByRaw("FIELD(role, 'admin', 'user')")
            ->orderBy('name')
            ->get()
            ->map(fn($u) => [
                'id'                    => $u->id,
                'name'                  => $u->name,
                'username'              => $u->username,
                'email'                 => $u->email,
                'role'                  => $u->role,
                'photo'                 => $u->photo ? asset('storage/' . $u->photo) : null,
                'orders_count'          => $u->orders_count,
                'stock_histories_count' => $u->stock_histories_count,
                'created_at'            => $u->created_at->format('d/m/Y'),
                'is_current_user'       => $u->id === Auth::id(),
            ]);

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username|alpha_dash',
            'email'    => 'required|email|max:150|unique:users,email',
            'role'     => 'required|in:admin,user',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'photo'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('avatars', 'public');
        }

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return back()->with('success', 'Akun berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email'    => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'role'     => 'required|in:admin,user',
            'photo'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $validated['photo'] = $request->file('photo')->store('avatars', 'public');
        } else {
            unset($validated['photo']);
        }

        // Proteksi: Akun yang sedang login tidak bisa mengubah rolenya sendiri
        if ($user->id === Auth::id()) {
            unset($validated['role']);
        }

        $user->update($validated);

        return back()->with('success', 'Akun berhasil diperbarui.');
    }

    public function updatePassword(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }

    public function destroy(User $user): RedirectResponse
    {
        // Proteksi: Tidak bisa hapus akun sendiri
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Proteksi: Tidak bisa hapus user yang punya riwayat transaksi (integritas data)
        if ($user->orders()->count() > 0) {
            return back()->with('error', "Akun '{$user->name}' memiliki riwayat transaksi dan tidak dapat dihapus.");
        }

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->delete();

        return back()->with('success', 'Akun berhasil dihapus.');
    }
}