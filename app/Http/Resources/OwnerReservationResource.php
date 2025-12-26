<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            // الحجز
            'check_in'  => $this->check_in,
            'check_out' => $this->check_out,

            'status' => [
                'key'   => $this->status->value,
                'label' => $this->status->label(),
            ],

            // تفاصيل
            'details' => [
                'adults'   => $this->details->adults_count,
                'children' => $this->details->children_count,
                'days'     => $this->details->days_count,
                'total'    => $this->details->total_price,
            ],

            // الشقة
            'apartment' => new ApartmentResource($this->apartment),

            // الزبون
            'tenant' => [
                'id' => $this->user->id,
                'name' => $this->user->first_name . ' ' . $this->user->last_name,
                'profile_image' => asset("storage/users/{$this->user->profile_image}")
            ],

            // آخر حجز قبله
            'previous_reservation' => $this->when(
                $this->previous_reservation,
                fn () => [
                    'check_out' => $this->previous_reservation->check_out,
                ]
            ),

            // الفجوة
            'gap_days_before' => $this->gap_days_before,
        ];
    }
}
