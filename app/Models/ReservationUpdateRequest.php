<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationUpdateRequest extends Model
{
    protected $fillable = [
        'reservation_id',
        'new_check_in',
        'new_check_out',
        'new_adults_count',
        'new_children_count',
        'new_days_count',
        'new_total_cost',
        'status',
    ];

    protected $casts = [
        'check_in' => 'date:Y-m-d',
        'check_out' => 'date:Y-m-d',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
