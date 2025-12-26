<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\ReservationService;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Utilities\ApiResponseService;
use App\Models\Apartment;
use App\Http\Resources\ReservationResource;
use App\Http\Resources\OwnerReservationResource;

class ReservationController extends Controller
{
    public function __construct(
        private ReservationService $reservationService
    ) {}

    public function store(StoreReservationRequest $request, Apartment $apartment)
    {
        $this->authorize('create', Reservation::class);
        $data = $request->validated();
        $reservation = $this->reservationService->store($apartment, $data);
        return ApiResponseService::createdResponse(
            data: $reservation
        );
    }

    public function update(UpdateReservationRequest $request, Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        $reservation = $this->reservationService
            ->update($reservation, $request->validated());

        return ApiResponseService::successResponse(
            ReservationResource::make($reservation)
        );
    }


    public function cancel(Reservation $reservation)
    {
        $this->authorize('delete', $reservation);

        $this->reservationService->cancel($reservation);

        return ApiResponseService::successResponse(
            msg: 'تم إلغاء الحجز بنجاح'
        );
    }

    public function ownerPendingReservations()
    {
        $reservations = $this->reservationService->pendingReservationsForOwner();

        return ApiResponseService::successResponse(
            OwnerReservationResource::collection($reservations)
        );
    }
}
