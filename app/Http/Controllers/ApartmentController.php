<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApartmentRequest;
use App\Models\Apartment;
use Illuminate\Http\Request;
use App\Services\ApartmentService;
use App\Utilities\ApiResponseService;
use App\Http\Resources\ApartmentResource;


class ApartmentController extends Controller
{
     public function __construct(
        private ApartmentService $apartmentService
    ) {}

     public function dashboard()
    {
        $data = $this->apartmentService->dashboard();

        return ApiResponseService::successResponse([
            'featured_apartments' => ApartmentResource::collection($data['featured']),
            'latest_apartments'   => ApartmentResource::collection($data['latest']),
        ]);
    }

    public function store(StoreApartmentRequest $request)
    {
        $apartment = $this->apartmentService->store($request->validated());
    }
    
}
