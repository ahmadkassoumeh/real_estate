<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Enums\ReservationStatusEnum;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\ReservationStatusNotification;


class CompleteReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:complete-reservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $reservations = Reservation::where('status', ReservationStatusEnum::APPROVED)
            ->whereDate('check_out', '<=', now())
            ->get();

        foreach ($reservations as $reservation) {
            $reservation->update([
                'status' => ReservationStatusEnum::COMPLETED,
            ]);

            $reservation->user->notify(
                new ReservationStatusNotification(
                    $reservation,
                    'تم إكمال الحجز، نأمل أن تكون التجربة جيدة'
                )
            );

            $reservation->apartment->owner->notify(
                new ReservationStatusNotification(
                    $reservation,
                    'تم إكمال حجز على شقتك'
                )
            );
        }
    }
}
