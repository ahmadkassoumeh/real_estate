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
use App\Http\Resources\TenantReservationResource;

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

    public function approve(Reservation $reservation)
    {
        $this->authorize('approve', $reservation);

        $reservation = $this->reservationService->approve($reservation);

        return ApiResponseService::successResponse(
            data: new OwnerReservationResource($reservation),
            msg: 'تمت الموافقة على الحجز'
        );
    }

    public function reject(Reservation $reservation)
    {
        $this->authorize('reject', $reservation);

        $reservation = $this->reservationService->reject($reservation);

        return ApiResponseService::successResponse(
            msg: 'تم رفض الحجز'
        );
    }

    public function reservedDates(Apartment $apartment)
    {
        return ApiResponseService::successResponse([
            'apartment_id'   => $apartment->id,
            'reserved_dates' => $this->reservationService->reservedDates($apartment),
        ]);
    }

    public function ownerPendingReservations()
    {
        $reservations = $this->reservationService->pendingReservationsForOwner();

        return ApiResponseService::successResponse(
            OwnerReservationResource::collection($reservations)
        );
    }

    public function history(ReservationService $service)
    {
        $data = $this->reservationService->tenantReservationHistory();

        return response()->json([
            'current_reservation' => $data['current']
                ? TenantReservationResource::collection($data['current'])
                : null,

            'previous_reservations' =>
            TenantReservationResource::collection($data['previous']),
        ]);
    }
}
