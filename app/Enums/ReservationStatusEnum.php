<?php

namespace App\Enums;

enum ReservationStatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
    case REJECTED  = 'rejected'; 

    public function label(): string
    {
        return trans("enums.reservation_status.$this->value");
    }
}
