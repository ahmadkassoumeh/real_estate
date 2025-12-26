<?php

namespace App\Services;

use App\Models\Apartment;
use Illuminate\Support\Facades\Auth;

class ApartmentService
{

    public function dashboard()
    {
        return [
            'featured' => Apartment::with(['mainImage', 'area'])
                ->featured()
                ->take(3)
                ->get(),

            'latest' => Apartment::with(['mainImage', 'area'])
                ->latestApartments()
                ->take(3)
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
    
}
