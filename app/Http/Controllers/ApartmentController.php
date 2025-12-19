<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApartmentRequest;
use App\Models\Apartment;
use Illuminate\Http\Request;
use App\Services\ApartmentService;

class ApartmentController extends Controller
{
     public function __construct(
        private ApartmentService $apartmentService
    ) {}


    public function store(StoreApartmentRequest $request)
    {
        $apartment = $this->apartmentService->store($request->validated());
    }
    
}
