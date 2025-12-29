<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApartmentRequest;
use App\Models\Apartment;
use Illuminate\Http\Request;
use App\Services\ApartmentService;
use App\Utilities\ApiResponseService;
use App\Http\Requests\FilterApartmentRequest;
use App\Http\Resources\ApartmentResource;
use App\Models\Reservation;

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
        $this->authorize('create', Apartment::class);

        $data = $request->validated();
        $apartment = $this->apartmentService->store($data);
        return ApiResponseService::createdResponse(
            data: new ApartmentResource($apartment)
        );
    }

    public function filter(FilterApartmentRequest $request)
    {
        // return response()->json($request->all());
        $apartments = $this->apartmentService->filter(
            $request->validated()
        );

        return ApiResponseService::successResponse(
            ApartmentResource::collection($apartments)
        );
    }

    public function storeReview(Request $request, Reservation $reservation)
    {
        $data = $request->all();
        $review = $this->apartmentService->storeReview($reservation, $data);
        return ApiResponseService::createdResponse(
            data: "تم التقييم بنجاح",
            code: 200
        );
    }

}
