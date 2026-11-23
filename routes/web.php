<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaundryOrderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegistration'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'role'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/notifications/{activityNotification}/read', [NotificationController::class, 'read'])
        ->name('notifications.read');

    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::post('/appointments', [AppointmentController::class, 'store'])
        ->middleware('role:customer')
        ->name('appointments.store');
    Route::patch('/appointments/{appointment}/review', [AppointmentController::class, 'review'])
        ->middleware('role:admin,staff')
        ->name('appointments.review');
    Route::post('/appointments/{appointment}/check-in', [AppointmentController::class, 'convertToOrder'])
        ->middleware('role:admin,staff')
        ->name('appointments.check-in');

    Route::get('/orders', [LaundryOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [LaundryOrderController::class, 'create'])
        ->middleware('role:admin,staff')
        ->name('orders.create');
    Route::post('/orders', [LaundryOrderController::class, 'store'])
        ->middleware('role:admin,staff')
        ->name('orders.store');
    Route::get('/orders/{laundryOrder}', [LaundryOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{laundryOrder}/status', [LaundryOrderController::class, 'updateStatus'])
        ->middleware('role:admin,staff')
        ->name('orders.status');
    Route::post('/orders/{laundryOrder}/payments', [LaundryOrderController::class, 'storePayment'])
        ->middleware('role:admin,staff')
        ->name('orders.payments.store');
    Route::get('/reports', [ReportController::class, 'index'])
        ->middleware('role:admin,staff')
        ->name('reports');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/users', [AdminController::class, 'users'])->name('users.index');
        Route::post('/staff', [AdminController::class, 'storeStaff'])->name('staff.store');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::patch('/users/{user}/toggle', [AdminController::class, 'toggleUser'])->name('users.toggle');
        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [BackupController::class, 'store'])->name('backups.store');
        Route::delete('/backups/{backup}', [BackupController::class, 'destroy'])->name('backups.destroy');
        Route::get('/backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
    });
});
