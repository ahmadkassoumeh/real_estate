<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Enums\ReservationStatusEnum;

class ReservationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Reservation $reservation): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    // إنشاء حجز
    public function create(User $user)
    {
        return $user->hasRole('tenant');
    }

    // تعديل الحجز
    public function update(User $user, Reservation $reservation)
    {
        return
            $user->hasRole('tenant') &&
            $reservation->user_id === $user->id;
    }

    // إلغاء الحجز
    public function delete(User $user, Reservation $reservation)
    {
        return
            $user->hasRole('tenant') &&
            $reservation->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Reservation $reservation): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Reservation $reservation): bool
    {
        return false;
    }

    public function approve(User $user, Reservation $reservation): bool
    {
        return
            $user->hasRole('owner') &&
            $reservation->apartment->owner_id === $user->id &&
            $reservation->status === ReservationStatusEnum::PENDING;
    }

    public function reject(User $user, Reservation $reservation): bool
    {
        return
            $user->hasRole('owner') &&
            $reservation->apartment->owner_id === $user->id &&
            $reservation->status === ReservationStatusEnum::PENDING;
    }
}
