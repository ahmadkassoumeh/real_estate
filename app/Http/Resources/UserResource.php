<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'phone_number' => $this->phone_number ?? 'لا يوجد',
            'username' => $this->username,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'date_of_birth' => $this->date_of_birth,

            'profile_image' => $this->profile_image
                ? asset("storage/users/{$this->profile_image}")
                : null,

            'id_card_image' => $this->id_card_image
                ? asset("storage/users/{$this->id_card_image}")
                : null,

            'role' => $this->getRoleNames()->first(),

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
