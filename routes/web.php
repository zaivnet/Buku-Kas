<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OutletController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Root redirect ke dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Route terautentikasi untuk semua role (Dashboard, Transaksi, Laporan, Profile)
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Transaksi Shortcuts & Resource
    Route::get('/transactions/income', [TransactionController::class, 'income'])->name('transactions.income');
    Route::get('/transactions/expense', [TransactionController::class, 'expense'])->name('transactions.expense');
    Route::resource('transactions', TransactionController::class);

    // Laporan Keuangan & Export
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/excel', [ReportController::class, 'excel'])->name('reports.export.excel');
    Route::get('/reports/export/pdf', [ReportController::class, 'pdf'])->name('reports.export.pdf');
    Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');

    // Profil User
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Route khusus Admin (User, Outlet, Kategori Management)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Outlet Management
    Route::patch('outlets/{outlet}/toggle-status', [OutletController::class, 'toggleStatus'])->name('outlets.toggle-status');
    Route::resource('outlets', OutletController::class);

    // User Management
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::resource('users', UserController::class);

    // Category Management
    Route::patch('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::resource('categories', CategoryController::class)->except(['show', 'create']);
});

require __DIR__.'/auth.php';
