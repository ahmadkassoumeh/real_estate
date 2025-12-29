<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class FilterApartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'check_in'  => ['nullable', 'date', 'after_or_equal:today'],
            'check_out' => ['nullable', 'date', 'after:check_in'],

            'space'       => ['nullable', 'integer', 'min:1'],
            'rooms_count' => ['nullable', 'integer', 'min:1'],
            'direction'   => ['nullable', 'in:north,south,east,west'],

            'price' => ['nullable', 'numeric', 'min:0'],

            'area_id'        => ['nullable', 'exists:areas,id'],
            'governorate_id' => ['nullable', 'exists:governorates,id'],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {

            if (
                ($this->filled('check_in') && !$this->filled('check_out')) ||
                (!$this->filled('check_in') && $this->filled('check_out'))
            ) {
                $validator->errors()->add(
                    'check_in',
                    'يجب إرسال تاريخ الدخول والخروج معاً'
                );
            }
        });
    }
}
