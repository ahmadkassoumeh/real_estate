<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'message' => $this->data['message'],
            'reservation_id' => $this->data['reservation_id'] ?? null,
            'apartment_id' => $this->data['apartment_id'] ?? null,
            'status' => $this->data['status'] ?? null,
            'is_read' => !is_null($this->read_at),
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
