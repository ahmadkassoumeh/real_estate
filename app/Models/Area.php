<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = ['name', 'governorate_id', 'is_featured'];

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function apartments()
    {
        return $this->hasMany(Apartment::class);
    }
}
