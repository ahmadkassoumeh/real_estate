<?php

namespace App\Services;

use App\Models\Favorite;
use App\Models\Apartment;
use App\Models\User;

class FavoriteService
{
    /* ================== Add to favorites ================== */
    public function toggle(User $user, Apartment $apartment): bool
    {
        $favorite = Favorite::where('user_id', $user->id)
            ->where('apartment_id', $apartment->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return false; // removed
        }

        Favorite::create([
            'user_id' => $user->id,
            'apartment_id' => $apartment->id,
        ]);

        return true; // added
    }

    /* ================== List favorites ================== */
    public function list(User $user)
    {
        return $user->favoriteApartments()
            ->with([
                'area',
                'images',
            ])
            ->latest()
            ->get();
    }
}
