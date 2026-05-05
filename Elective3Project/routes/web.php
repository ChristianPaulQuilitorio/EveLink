<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendeeAuthController;
use App\Http\Controllers\AttendeePortalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');

Route::prefix('portal')->name('portal.')->group(function (): void {
    Route::get('/', [AttendeePortalController::class, 'index'])->name('home');
    Route::get('/events/{event}', [AttendeePortalController::class, 'show'])->name('events.show');

    Route::get('/login', [AttendeeAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AttendeeAuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AttendeeAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AttendeeAuthController::class, 'register'])->name('register.store');

    Route::middleware(['auth', 'role:attendee'])->group(function (): void {
        Route::post('/events/{event}/join', [AttendeePortalController::class, 'join'])->name('events.join');
        Route::post('/events/{event}/quit', [AttendeePortalController::class, 'quit'])->name('events.quit');
        Route::get('/my-registrations', [AttendeePortalController::class, 'registrations'])->name('registrations');
        Route::post('/logout', [AttendeeAuthController::class, 'logout'])->name('logout');
    });
});

Route::middleware(['auth', 'role:admin'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('events', EventController::class);
    Route::resource('registrations', RegistrationController::class)->except(['show']);

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::patch('/attendance/{registration}', [AttendanceController::class, 'update'])->name('attendance.update');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
