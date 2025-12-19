<?php

namespace App\Services;

use App\Models\Apartment;
use Illuminate\Support\Facades\Auth;

class ApartmentService
{


    public function store($request): Apartment
    {
        $apartment = Apartment::create([
            'owner_id' => Auth::id(),
            'area_id' => $request->area_id,
            'price' => $request->price,
            'space' => $request->space,
            'rooms_count' => $request->rooms_count,
            'direction' => $request->direction,
            'description' => $request->description,
        ]);

        $ownerId = Auth::id();

        foreach ($request->file('images') as $image) {
            $path = $image->store(
                "{$ownerId}/{$apartment->id}",
                'apartment'
            );

            $apartment->images()->create([
                'path' => $path,
            ]);
        }

        return $apartment;
    }

    
}
