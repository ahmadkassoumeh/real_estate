<?php

namespace App\Services;

use App\Models\Apartment;
use App\Models\Reservation;
use App\Models\ApartmentReview;
use App\Enums\ReservationStatusEnum;
use Illuminate\Support\Facades\Auth;

class ApartmentService
{

    public function dashboard()
    {
        return [
            'featured' => Apartment::with(['mainImage', 'area'])
                ->featured()
                ->get(),

            'latest' => Apartment::with(['mainImage', 'area'])
                ->latestApartments()
                ->get(),
        ];
    }

    public function store(array $data): Apartment
    {

        $apartment = Apartment::create([
            'owner_id' => Auth::id(),
            'area_id' => $data['area_id'],
            'price' => $data['price'],
            'space' => $data['space'],
            'rooms_count' => $data['rooms_count'],
            'direction' => $data['direction'],
            'description' => $data['description'],
        ]);

        foreach ($data['images'] as $image) {
            $path = $image->store(
                Auth::id() . '/' . $apartment->id,
                'apartment'
            );

            $apartment->images()->create([
                'path' => $path,
            ]);
        }

        return $apartment;
    }

    public function filter(array $filters)
    {
        $query = Apartment::query()
            ->with(['images', 'area.governorate']);

        // 🔹 فلترة التوفر بالتواريخ
        if (!empty($filters['check_in']) && !empty($filters['check_out'])) {

            $checkIn  = $filters['check_in'];
            $checkOut = $filters['check_out'];

            $query->whereDoesntHave('reservations', function ($q) use ($checkIn, $checkOut) {
                $q->whereIn('status', [
                    ReservationStatusEnum::PENDING->value,
                    ReservationStatusEnum::APPROVED->value,
                ])
                    ->where(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in', '<', $checkOut)
                            ->where('check_out', '>', $checkIn);
                    });
            });
        }

        // 🔹 المساحة
        if (!empty($filters['space'])) {
            $query->where('space', '>=', $filters['space']);
        }

        // 🔹 عدد الغرف
        if (!empty($filters['rooms_count'])) {
            $query->where('rooms_count', $filters['rooms_count']);
        }

        // 🔹 الاتجاه
        if (!empty($filters['direction'])) {
            $query->where('direction', $filters['direction']);
        }

        if (!empty($filters['price'])) {
            $query->where('price', '<=', $filters['price']);
        }


        // 🔹 المنطقة
        if (!empty($filters['area_id'])) {
            $query->where('area_id', $filters['area_id']);
        }

        // 🔹 المحافظة (من خلال المنطقة)
        if (!empty($filters['governorate_id'])) {
            $query->whereHas('area', function ($q) use ($filters) {
                $q->where('governorate_id', $filters['governorate_id']);
            });
        }

        return $query->get();
    }

    public function storeReview(Reservation $reservation, array $data): ApartmentReview
    {
        // تحقق بسيط (بدون FormRequest)
        if (!isset($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
            throw new \Exception('التقييم يجب أن يكون بين 1 و 5');
        }

        if ($reservation->review) {
            throw new \Exception('تم تقييم هذا الحجز مسبقاً');
        }

        if ($reservation->user_id !== Auth::id()) {
            throw new \Exception('غير مصرح');
        }

        if ($reservation->status !== \App\Enums\ReservationStatusEnum::COMPLETED) {
            throw new \Exception('لا يمكن التقييم قبل انتهاء الحجز');
        }

        return ApartmentReview::create([
            'reservation_id' => $reservation->id,
            'apartment_id' => $reservation->apartment_id,
            'user_id' => Auth::id(),
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);
    }

}
