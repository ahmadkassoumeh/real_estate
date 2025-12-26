<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Reservation;

class ReservationStatusNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
   public function __construct(
        private Reservation $reservation,
        private string $message
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'reservation_id' => $this->reservation->id,
            'status' => $this->reservation->status->value,
            'message' => $this->message,
        ];
    }
}
