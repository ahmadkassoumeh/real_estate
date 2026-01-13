<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\NotificationController;
use Spatie\Permission\Middleware\RoleMiddleware;
use App\Http\Controllers\LocationController;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FriendController;

// 🔓 بدون توكن
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// 🔐 مع توكن (Passport)
Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);
    Route::get('status', [AuthController::class, 'status']);

    ////^  Apartment  ////
    Route::get('dashboard', [ApartmentController::class, 'dashboard']);
    Route::post('apartments', [ApartmentController::class, 'store']);
    Route::post('apartments/filter', [ApartmentController::class, 'filter'])->middleware(RoleMiddleware::class . ':tenant');

    Route::post(
        '/reservations/{reservation}/review',
        [ApartmentController::class, 'storeReview']
    );

    Route::get('tenant/reservations/history', [ReservationController::class, 'history'])->middleware(RoleMiddleware::class . ':tenant');


    //*  Reservation  *//
    Route::post('apartments/{apartment}/reservations', [ReservationController::class, 'store']);
    Route::post('reservations/{reservation}', [ReservationController::class, 'update']);
    Route::post('reservations_cancel/{reservation}', [ReservationController::class, 'cancel']);
    Route::get('apartments/{apartment}/reserved-dates', [ReservationController::class, 'reservedDates']);
    Route::post(
        'reservation-update-requests/{reservationUpdateRequest}/approve',
        [ReservationController::class, 'approvedReservationUpdateRequest']
    );

    Route::post(
        'reservation-update-requests/{reservationUpdateRequest}/reject',
        [ReservationController::class, 'rejectedReservationUpdateRequest']
    );

    Route::get('list-reservation-update-requests', [ReservationController::class, 'updateRequests']);


    ////&  Notification  &////
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/unread', [NotificationController::class, 'unread']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);

    // Location
    Route::get(
        'locations',
        [LocationController::class, 'index']
    );

    //***** Chat *****//
    Route::post('/addfriend', [FriendController::class, 'add']);
    Route::get('/users', [ChatController::class, 'users']);
    Route::post('/messages/send', [ChatController::class, 'sendMessage']);
    Route::get('/messages/{userId}', [ChatController::class, 'getMessages']);
    Route::get('/messages/unread/count', [ChatController::class, 'unreadCount']);
});

////////////!!  Owner  !!//////////
Route::middleware(['auth:api', RoleMiddleware::class . ':owner'])->group(function () {

    Route::get(
        'owner/dashboard',
        [ReservationController::class, 'ownerPendingReservations']
    );

    Route::post(
        'owner/reservations/{reservation}/approve',
        [ReservationController::class, 'approve']
    );

    Route::post(
        'owner/reservations/{reservation}/reject',
        [ReservationController::class, 'reject']
    );
});
