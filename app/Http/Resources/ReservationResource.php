<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            // التواريخ
            'check_in'  => $this->check_in->format('Y-m-d'),
            'check_out' => $this->check_out->format('Y-m-d'),

            // تفاصيل الحجز
            'adults_count'   => $this->details->adults_count,
            'children_count' => $this->details->children_count,

            // محسوبة
            'days_count' => $this->details->days_count,
            'total_cost' => $this->details->total_price,

            'status' => [
                'key'   => $this->status->value,
                'label' => $this->status->label(),
            ],

            // الشقة
            'apartment' => new ApartmentResource($this->whenLoaded('apartment')),

            // المستأجر
            'tenant' => [
                'id' => $this->user->id,
                'name' => $this->user->first_name . ' ' . $this->user->last_name,
            ],


            'created_at' => $this->created_at,
        ];
    }
}
