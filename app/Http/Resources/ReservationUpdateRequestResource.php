<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationUpdateRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $reservation = $this->reservation;
        $details     = $reservation->details;

        return [
            'request_id' => $this->id,

            'status' => [
                'key'   => $this->status,
                'label' => match ($this->status) {
                    'pending'  => 'بانتظار الموافقة',
                    'approved' => 'مقبول',
                    'rejected' => 'مرفوض',
                },
            ],

            // الحجز
            'reservation' => [
                'id' => $reservation->id,

                'dates' => [
                    'check_in' => [
                        'old' => $reservation->check_in->format('Y-m-d'),
                        'new' => $this->new_check_in,
                    ],
                    'check_out' => [
                        'old' => $reservation->check_out->format('Y-m-d'),
                        'new' => $this->new_check_out,
                    ],
                ],
            ],

            // التفاصيل
            'details' => [
                'adults' => [
                    'old' => $details->adults_count,
                    'new' => $this->new_adults_count,
                ],
                'children' => [
                    'old' => $details->children_count,
                    'new' => $this->new_children_count,
                ],
                'days' => [
                    'old' => $details->days_count,
                    'new' => $this->new_days_count,
                ],
                'total_cost' => [
                    'old' => $details->total_cost,
                    'new' => $this->new_total_cost,
                ],
            ],

            // الشقة
            'apartment' => new ApartmentResource($reservation->apartment),

            // المستأجر
            'tenant' => [
                'id'   => $reservation->user->id,
                'name' => $reservation->user->first_name . ' ' . $reservation->user->last_name,
                'profile_image' => $reservation->user->profile_image
                    ? asset("storage/users/{$reservation->user->profile_image}")
                    : null,
            ],

            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
