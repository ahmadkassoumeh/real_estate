<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\Reservation;
use App\Enums\ReservationStatusEnum;

class StoreReservationRequest extends FormRequest
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
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'adults_count' => ['required', 'integer', 'min:1'],
            'children_count' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            // ⛔ تأكد أن التواريخ موجودة
            if (! $this->filled(['check_in', 'check_out'])) {
                return;
            }

            $apartmentId = $this->route('apartment')->id;
            $checkIn  = $this->check_in;
            $checkOut = $this->check_out;

            $hasConflict = Reservation::where('apartment_id', $apartmentId)
                ->whereIn('status', [
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
                    'هذه الشقة محجوزة خلال الفترة المحددة'
                );
            }
        });
    }
}
