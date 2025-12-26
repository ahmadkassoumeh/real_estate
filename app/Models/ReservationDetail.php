<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationDetail extends Model
{
    protected $fillable = [
        'reservation_id',
        'adults_count',
        'children_count',
        'days_count',
        'total_cost',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}

