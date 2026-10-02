<?php

use App\Http\Controllers\Admin\AdminCourierController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminServiceController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Api\TrackingApiController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Courier\CourierDashboardController;
use App\Http\Controllers\Courier\CourierTaskController;
use App\Http\Controllers\Customer\CustomerAddressController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Customer\CustomerProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public Root Redirect
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();

        return match ($user->role->value ?? (string) $user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'courier' => redirect()->route('courier.dashboard'),
            'customer' => redirect()->route('customer.dashboard'),
            default => redirect()->route('login'),
        };
    }

    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Customer Protected Routes
Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders/create', [CustomerOrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [CustomerOrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/tracking', [CustomerOrderController::class, 'tracking'])->name('orders.tracking');
    Route::get('/history', [CustomerOrderController::class, 'history'])->name('orders.history');
    Route::post('/orders/{order}/pay', [CustomerOrderController::class, 'pay'])->name('orders.pay');
    Route::patch('/orders/{order}/cancel', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/profile', [CustomerProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [CustomerProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [CustomerProfileController::class, 'updatePassword'])->name('profile.password');

    // Customer Address Book
    Route::resource('addresses', CustomerAddressController::class);
    Route::patch('/addresses/{address}/default', [CustomerAddressController::class, 'setDefault'])->name('addresses.default');
});

// Courier Protected Routes
Route::middleware(['auth', 'role:courier'])->prefix('courier')->name('courier.')->group(function () {
    Route::get('/dashboard', [CourierDashboardController::class, 'index'])->name('dashboard');
    Route::patch('/profile/status', [CourierDashboardController::class, 'updateProfileStatus'])->name('profile.status');
    Route::get('/tasks/{assignment}', [CourierTaskController::class, 'show'])->name('tasks.show');
    Route::patch('/tasks/{assignment}/start', [CourierTaskController::class, 'start'])->name('tasks.start');
    Route::patch('/tasks/{assignment}/complete', [CourierTaskController::class, 'complete'])->name('tasks.complete');
    Route::get('/history', [CourierTaskController::class, 'history'])->name('history');
});

// Admin Protected Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Orders Management
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/confirm', [AdminOrderController::class, 'confirm'])->name('orders.confirm');
    Route::patch('/orders/{order}/weight', [AdminOrderController::class, 'updateWeight'])->name('orders.weight');
    Route::post('/orders/{order}/assign-courier', [AdminOrderController::class, 'assignCourier'])->name('orders.assign-courier');
    Route::patch('/orders/{order}/receive', [AdminOrderController::class, 'receiveAtLaundry'])->name('orders.receive');
    Route::patch('/orders/{order}/stage', [AdminOrderController::class, 'updateStage'])->name('orders.stage');
    Route::patch('/payments/{payment}/confirm', [AdminOrderController::class, 'confirmPayment'])->name('payments.confirm');

    // Services Catalog CRUD
    Route::resource('services', AdminServiceController::class);

    // Courier Management
    Route::get('/couriers', [AdminCourierController::class, 'index'])->name('couriers.index');
    Route::get('/couriers/create', [AdminCourierController::class, 'create'])->name('couriers.create');
    Route::post('/couriers', [AdminCourierController::class, 'store'])->name('couriers.store');
    Route::patch('/couriers/{courier}/toggle', [AdminCourierController::class, 'toggleStatus'])->name('couriers.toggle');
    Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [AdminCustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/{customer}/edit', [AdminCustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [AdminCustomerController::class, 'update'])->name('customers.update');
    Route::patch('/customers/{customer}/toggle', [AdminCustomerController::class, 'toggle'])->name('customers.toggle');

    // Business Reports & Analytics
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/settings', [AdminSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
});

// AJAX Live Tracking Endpoints
Route::middleware('auth')->prefix('api')->name('api.')->group(function () {
    Route::post('/courier/location', [TrackingApiController::class, 'updateLocation'])->name('courier.location');
    Route::get('/orders/{order}/tracking', [TrackingApiController::class, 'getOrderTracking'])->name('orders.tracking');
});
