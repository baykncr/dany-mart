<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;   
 
Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    // ── DASHBOARD ──────────────────────────────────────────────────────
    Route::get('/dashboard', function (Request $request) {
        $user = $request->user();
        $isAdmin = $user->role === 'admin' 
                || $user->role === 'Admin' 
                || (method_exists($user, 'hasRole') && $user->hasRole('admin'));

        if ($isAdmin) {
            return app(DashboardController::class)->index($request);
        }
        return redirect()->route('pos.index');
        
    })->name('dashboard');
    // ── KASIR & CHECKOUT (Semua Role Bisa Akses) ───────────────────────
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/checkout', [OrderController::class, 'store'])->name('pos.checkout');
    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('products', ProductController::class)->only(['index', 'store', 'update', 'destroy']);

    // ── RIWAYAT TRANSAKSI (Semua Role Bisa Lihat) ──────────────────────
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');

    // ── ADMIN ONLY ─────────────────────────────────────────────────────
    Route::middleware('admin')->group(function () {
        

        // Akses Role Management
        Route::get('/roles', [App\Http\Controllers\RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles/{role}/toggle', [App\Http\Controllers\RoleController::class, 'toggle'])->name('roles.toggle');

        // PERBAIKAN: Tambahkan ini untuk menu "Kelola Akun"
        Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);

        // Export Excel 
        Route::get('/transactions/export', [TransactionController::class, 'export'])->name('transactions.export');
    });
});

if (file_exists(__DIR__.'/settings.php')) {
    require __DIR__.'/settings.php';
}

if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
}