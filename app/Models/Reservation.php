<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\ReservationStatusEnum;

class Reservation extends Model
{
    protected $fillable = [
        'apartment_id',
        'user_id',
        'check_in',
        'check_out',
        'status'
    ];

    protected $casts = [
        'status' => ReservationStatusEnum::class,
        'check_in' => 'date',
        'check_out' => 'date',
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
        return $query->where('status', ReservationStatusEnum::APPROVED)
            ->whereDate('check_in', '<=', now())
            ->whereDate('check_out', '>=', now());
    }

    public function scopePast($query)
    {
        return $query->where('status', ReservationStatusEnum::COMPLETED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', ReservationStatusEnum::CANCELLED);
    }

    public function details()
    {
        return $this->hasOne(ReservationDetail::class);
    }
}
