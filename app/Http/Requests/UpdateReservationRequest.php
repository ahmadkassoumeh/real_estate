<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\Reservation;
use App\Enums\ReservationStatusEnum;

class UpdateReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'check_in' => ['nullable', 'date', 'after_or_equal:today'],
            'check_out' => ['nullable', 'date', 'after:check_in'],
            'adults_count' => ['nullable', 'integer', 'min:1'],
            'children_count' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {

            $reservation = $this->route('reservation');

            //  إذا ما في تعديل على التاريخ → لا نتحقق من التعارض
            if (! $this->filled('check_in') && ! $this->filled('check_out')) {
                return;
            }

            $apartmentId = $reservation->apartment_id;

            // 🟢 تحديد القيم النهائية للتاريخ
            $checkIn = $this->filled('check_in')
                ? $this->check_in
                : $reservation->check_in;

            $checkOut = $this->filled('check_out')
                ? $this->check_out
                : $reservation->check_out;


            if (! $checkIn || ! $checkOut) {
                return;
            }

            $hasConflict = Reservation::where('apartment_id', $apartmentId)
                ->where('id', '!=', $reservation->id)
                ->whereIn('status', [
                    ReservationStatusEnum::PENDING->value,
                    ReservationStatusEnum::APPROVED->value,
                ])
                ->where(function ($query) use ($checkIn, $checkOut) {
                    $query->where('check_in', '<', $checkOut)
                        ->where('check_out', '>', $checkIn);
                })
                ->exists();

            if ($hasConflict) {
                $validator->errors()->add(
                    'check_in',
                    'الفترة الجديدة تتعارض مع حجز آخر'
                );
            }
        });
    }

}
