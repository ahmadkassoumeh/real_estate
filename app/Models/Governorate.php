<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Governorate extends Model
{
    protected $fillable = ['name'];

    public function areas()
    {
        return $this->hasMany(Area::class);
    }
}
