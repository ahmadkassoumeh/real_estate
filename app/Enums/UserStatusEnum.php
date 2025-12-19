<?php

namespace App\Enums;

enum UserStatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return trans("enums.user_status.$this->value");
    }
}
