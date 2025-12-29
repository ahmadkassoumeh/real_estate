<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\ReservationStatusEnum;

class TenantReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'reservation_id' => $this->id,

            // التواريخ
            'check_in'  => $this->check_in,
            'check_out' => $this->check_out,

            // الحالة
            'status' => [
                'key'   => $this->status->value,
                'label' => $this->status->label(),
            ],

            // التفاصيل
            'details' => [
                'adults'   => $this->details->adults_count,
                'children' => $this->details->children_count,
                'days'     => $this->details->days_count,
                'total'    => $this->details->total_cost,
            ],

            // الشقة
            'apartment' => new ApartmentResource($this->apartment),

            // التقييم
            'review' => [
                'is_reviewed' => $this->review !== null,
                'data' => $this->when(
                    $this->review,
                    fn () => [
                        'rating' => $this->review->rating,
                        'comment' => $this->review->comment,
                    ]
                ),
            ],
        ];
    }
}
