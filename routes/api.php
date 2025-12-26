<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\NotificationController;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Models\Role;

// 🔓 بدون توكن
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// 🔐 مع توكن (Passport)
Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);
    Route::get('status', [AuthController::class, 'status']);
    Route::get('dashboard', [ApartmentController::class, 'dashboard']);
    Route::post('apartments', [ApartmentController::class, 'store']);

    //*  Reservation  *//
    Route::post('apartments/{apartment}/reservations',[ReservationController::class, 'store']);
    Route::post('reservations/{reservation}',[ReservationController::class, 'update']);
    Route::post('reservations_cancel/{reservation}',[ReservationController::class, 'cancel']);


    ////&  Notification  &////
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/unread', [NotificationController::class, 'unread']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead']);

});



////////////!!  Owner  !!//////////
Route::middleware(['auth:api', RoleMiddleware::class . ':owner'])->group(function () {
    Route::get(
        'owner/dashboard',
        [ReservationController::class, 'ownerPendingReservations']
    );
});




Route::get(
    'apartments/{apartment}/reserved-dates',
    [ApartmentController::class, 'reservedDates']
);
