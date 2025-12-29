<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\ApartmentDirectionEnum;
use App\Enums\ReservationStatusEnum;

class Apartment extends Model
{
    protected $fillable = [
        'area_id',
        'owner_id',
        'price',
        'space',
        'direction',
        'rooms_count',
        'description',
    ];

    protected $casts = [
        'direction' => ApartmentDirectionEnum::class,
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function images()
    {
        return $this->hasMany(ApartmentImage::class);
    }

    public function reviews()
    {
        return $this->hasMany(ApartmentReview::class);
    }


    public function favorites()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }


    // الحجز الحالي
    public function currentReservation()
    {
        return $this->hasOne(Reservation::class)
            ->where('status', ReservationStatusEnum::APPROVED->value)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now());
    }

    public function mainImage()
    {
        return $this->hasOne(ApartmentImage::class)->where('is_main', true);
    }

    public function scopeFeatured($query)
    {
        return $query->whereHas('area', function ($q) {
            $q->where('is_featured', true);
        });
    }

    public function scopeLatestApartments($query)
    {
        return $query->latest();
    }

    public function details()
    {
        return $this->hasOne(ReservationDetail::class);
    }
}
