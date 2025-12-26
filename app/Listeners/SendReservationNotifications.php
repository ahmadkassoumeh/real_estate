<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\ReservationStatusChanged;
use App\Notifications\ReservationStatusNotification;

class SendReservationNotifications
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ReservationStatusChanged $event): void
    {
        $reservation = $event->reservation;

        // tenant
        $reservation->user->notify(
            new ReservationStatusNotification(
                $reservation,
                "تم تحديث حالة الحجز إلى {$reservation->status->label()}"
            )
        );

        // owner
        $reservation->apartment->owner->notify(
            new ReservationStatusNotification(
                $reservation,
                "تم تحديث حالة حجز لشقتك"
            )
        );
    }
}
