<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\ReservationStatusEnum;

class Reservation extends Model
{
    protected $fillable = [
        'apartment_id',
        'user_id',
        'start_date',
        'end_date',
        'status'
    ];

    protected $casts = [
        'status' => ReservationStatusEnum::class,
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeCurrent($query)
    {
        return $query->where('status', ReservationStatusEnum::ACTIVE)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now());
    }

    public function scopePast($query)
    {
        return $query->where('status', ReservationStatusEnum::COMPLETED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', ReservationStatusEnum::CANCELLED);
    }
}
