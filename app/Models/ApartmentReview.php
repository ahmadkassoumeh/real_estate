<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApartmentReview extends Model
{
    protected $fillable = [
        'reservation_id',
        'apartment_id',
        'user_id',
        'rating',
        'comment',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    protected $casts = [
        'rating' => 'integer',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
