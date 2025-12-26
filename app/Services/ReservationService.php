<?php

namespace App\Services;

use App\Models\Apartment;
use App\Models\Reservation;
use App\Models\User;
use App\Enums\ReservationStatusEnum;
use App\Models\ReservationDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Notifications\ReservationStatusNotification;

class ReservationService
{

    public function store(Apartment $apartment, array $data): Reservation
    {
        $days = Carbon::parse($data['check_in'])
            ->diffInDays(Carbon::parse($data['check_out']));

        $reservation = Reservation::create([
            'apartment_id' => $apartment->id,
            'user_id' => Auth::id(),
            'check_in' => $data['check_in'],
            'check_out' => $data['check_out'],
            'status' => ReservationStatusEnum::PENDING,
        ]);

        $reservation->details()->create([
            'adults_count' => $data['adults_count'],
            'children_count' => $data['children_count'] ?? 0,
            'days_count' => $days,
            'total_cost' => $days * $apartment->price,
        ]);

        // tenant
        $reservation->user->notify(
            new ReservationStatusNotification(
                $reservation,
                'تم إرسال طلب الحجز، بانتظار موافقة صاحب الشقة'
            )
        );

        // owner
        $reservation->apartment->owner->notify(
            new ReservationStatusNotification(
                $reservation,
                'لديك طلب حجز جديد على شقتك'
            )
        );

        return $reservation;
    }

    public function update(Reservation $reservation, array $data): Reservation
    {
        $days = Carbon::parse($data['check_in'])
            ->diffInDays(Carbon::parse($data['check_out']));

        $reservation->update([
            'check_in' => $data['check_in'],
            'check_out' => $data['check_out'],
        ]);

        $reservationDetails = ReservationDetail::where('reservation_id', $reservation->id)->first();

        $reservationDetails->update([
            'adults_count' => $data['adults_count'],
            'children_count' => $data['children_count'] ?? 0,
            'days_count' => $days,
            'total_cost' => $days * $reservation->apartment->price,
        ]);

        return $reservation->load([
            'apartment.area',
            'apartment.images',
            'user',
            'details',
        ]);
    }

    public function cancel(Reservation $reservation): void
    {
        $reservation->update([
            'status' => ReservationStatusEnum::CANCELLED,
            'cancelled_by' => Auth::id(),
        ]);

        $reservation->user->notify(
            new ReservationStatusNotification(
                $reservation,
                'تم إلغاء طلب الحجز الخاص بك'
            )
        );
    }

    public function pendingReservationsForOwner()
    {
        $ownerId = Auth::id();

        $reservations = Reservation::with([
            'apartment.images',
            'apartment.area.governorate',
            'user',
            'details',
        ])
            ->whereHas('apartment', function ($q) use ($ownerId) {
                $q->where('owner_id', $ownerId);
            })
            ->where('status', ReservationStatusEnum::PENDING->value)
            ->get();

        // نحسب الـ GAP لكل حجز
        return $reservations->map(function ($reservation) {
            $previous = Reservation::where('apartment_id', $reservation->apartment_id)
                ->where('status', ReservationStatusEnum::APPROVED->value)
                ->where('check_out', '<=', $reservation->check_in)
                ->orderBy('check_out', 'desc')
                ->first();

            $gapDays = null;

            if ($previous) {
                $gapDays = Carbon::parse($previous->check_out)
                    ->diffInDays(Carbon::parse($reservation->check_in));
            }

            // نضيفهم كـ attributes مؤقتة
            $reservation->previous_reservation = $previous;
            $reservation->gap_days_before = $gapDays;

            return $reservation;
        });
    }


    public function reservedDates(Apartment $apartment)
    {
        $reservations = $apartment->reservations()
            ->where('status', ReservationStatusEnum::APPROVED->value)
            ->get(['check_in', 'check_out']);

        $dates = [];

        foreach ($reservations as $reservation) {
            $period = \Carbon\CarbonPeriod::create(
                $reservation->check_in,
                $reservation->check_out->subDay() // لأن checkout غير محسوب
            );

            foreach ($period as $date) {
                $dates[] = $date->toDateString(); // 2025-12-21
            }
        }

        return response()->json([
            'apartment_id' => $apartment->id,
            'reserved_dates' => $dates
        ]);
    }
}
