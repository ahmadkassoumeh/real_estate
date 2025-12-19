<?php

namespace App\Enums;

enum ApartmentDirectionEnum: string
{
    case NORTH = 'north';
    case SOUTH = 'south';
    case EAST  = 'east';
    case WEST  = 'west';

    public function label(): string
    {
        return trans("enums.apartment_direction.$this->value");
    }
}
