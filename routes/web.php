<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// Halaman awal: tamu -> login, user login -> halaman sesuai role
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(auth()->user()->homeRoute())
        : redirect()->route('login');
});

// ---------- Auth ----------
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.process');
});
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// ---------- Admin saja ----------
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', UserController::class)->except('show');        // admin.users.*
    Route::resource('services', ServiceController::class)->except('show');  // admin.services.*
});

// ---------- Staff (+ admin) : order & tracking ----------
Route::middleware(['auth', 'role:admin,staff'])->group(function () {
    Route::get('/staff/dashboard', [OrderController::class, 'index'])->name('staff.dashboard');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::patch('/orders/{order}/payment', [OrderController::class, 'verifyPayment'])->name('orders.payment');

    Route::get('/orders/{order}/edit', [OrderController::class, 'edit'])->whereNumber('order')->name('orders.edit');
    Route::put('/orders/{order}', [OrderController::class, 'update'])->whereNumber('order')->name('orders.update');

    // Customer: staff & admin (hapus hanya admin, lihat bagian bawah)
    Route::resource('customers', CustomerController::class)->except(['show', 'destroy']);
});

// ---------- Customer (+ admin) : riwayat milik sendiri ----------
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/riwayat', [OrderController::class, 'history'])->name('customer.orders');
});

// ---------- Detail order: semua user login, kepemilikan dicek di controller ----------
// Harus SETELAH /orders/create agar "create" tidak dianggap sebagai {order}
Route::get('/orders/{order}', [OrderController::class, 'show'])
    ->whereNumber('order')
    ->middleware('auth')
    ->name('orders.show');

Route::get('/orders/{order}/struk', [OrderController::class, 'receipt'])
    ->whereNumber('order')
    ->middleware('auth')
    ->name('orders.receipt');

// ---------- Hapus data (admin saja) ----------
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->whereNumber('order')->name('orders.destroy');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->whereNumber('customer')->name('customers.destroy');
});
