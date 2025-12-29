<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Governorate;
use App\Models\Area;

class LocationController extends Controller
{
    public function index()
    {
        return response()->json([
            'governorates' => Governorate::select('id', 'name')->get(),
            'areas' => Area::select('id', 'name', 'governorate_id')->get(),
        ]);
    }
}
