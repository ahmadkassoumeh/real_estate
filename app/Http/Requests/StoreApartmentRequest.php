<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Utilities\Helpers\EnumHelper;
use App\Enums\ApartmentDirectionEnum;

class StoreApartmentRequest extends FormRequest
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
            'area_id' => 'required|exists:areas,id',

            // 'governorate_id' => 'required|exists:governorates,id',

            // 'owner_id' => 'required|exists:users,id',

            'price' => 'required|numeric|min:0',

            'space' => 'required|integer|min:1',

            'rooms_count' => 'required|integer|min:1',

            'direction' => 'required|in:' .
                EnumHelper::getEnumValuesString(
                    ApartmentDirectionEnum::class,
                    ','
                ),

            'description' => 'required|string|max:1000',

            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp,jpeg|max:4096',
        ];
    }
}
