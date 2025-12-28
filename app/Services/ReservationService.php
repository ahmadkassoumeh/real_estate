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
use Carbon\CarbonPeriod;

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
        if ($reservation->status !== ReservationStatusEnum::PENDING->value) {
            throw new \Exception('لا يمكن الغاء هذا الحجز');
        }

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

    public function approve(Reservation $reservation): Reservation
    {
        if ($reservation->status !== ReservationStatusEnum::PENDING) {
            throw new \Exception('لا يمكن الموافقة على هذا الحجز');
        }

        $reservation->update([
            'status' => ReservationStatusEnum::APPROVED,
        ]);

        // tenant
        $reservation->user->notify(
            new ReservationStatusNotification(
                $reservation,
                'تم الموافقة على طلب الحجز'
            )
        );

        // owner
        $reservation->apartment->owner->notify(
            new ReservationStatusNotification(
                $reservation,
                'لقد وافقت على طلب الحجز'
            )
        );

        return $reservation->load([
            'apartment.images',
            'apartment.area.governorate',
            'user',
            'details',
        ]);
    }

    public function reject(Reservation $reservation): Reservation
    {
        if ($reservation->status !== ReservationStatusEnum::PENDING) {
            throw new \Exception('لا يمكن رفض هذا الحجز');
        }

        $reservation->update([
            'status' => ReservationStatusEnum::REJECTED,
        ]);

        // tenant
        $reservation->user->notify(
            new ReservationStatusNotification(
                $reservation,
                'تم الموافقة على طلب الحجز'
            )
        );

        // owner
        $reservation->apartment->owner->notify(
            new ReservationStatusNotification(
                $reservation,
                'لقد وافقت على طلب الحجز'
            )
        );

        return $reservation;
    }


    public function reservedDates(Apartment $apartment): array
    {
        $today = Carbon::today();

        $reservations = $apartment->reservations()
            ->where('status', ReservationStatusEnum::APPROVED->value)
            ->whereDate('check_out', '>=', $today) // من اليوم وطالع
            ->get(['check_in', 'check_out']);

        $dates = [];

        foreach ($reservations as $reservation) {

            $start = Carbon::parse($reservation->check_in);

            // إذا الحجز بلش قبل اليوم، نبدأ من اليوم
            if ($start->lt($today)) {
                $start = $today;
            }

            $period = CarbonPeriod::create(
                $start,
                Carbon::parse($reservation->check_out)->subDay()
            );

            foreach ($period as $date) {
                $dates[] = $date->toDateString();
            }
        }

        return array_values(array_unique($dates));
    }
}
