<?php

namespace App\Enums;

enum ReservationStatusEnum: string
{
    case ACTIVE = 'active';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return trans("enums.reservation_status.$this->value");
    }
}
