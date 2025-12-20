<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApartmentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'price'       => $this->price,
            'space'       => $this->space,
            'rooms_count' => $this->rooms_count,
            'direction'   => $this->direction->value,
            'description' => $this->description,

            'governorate' => [
                'name' => $this->area->governorate->name,
            ],

            'area' => [
                'name' => $this->area->name,
                'is_featured' => $this->area->is_featured,
            ],

            'images' => $this->images->map(function ($image) {
                return [
                    'url'     => asset('storage/apartment/' . $image->path),
                    'is_main' => $image->is_main,
                ];
            }),
        ];

    }

}
