<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Services\FavoriteService;
use App\Http\Resources\ApartmentResource;
use Illuminate\Http\Request;
use App\Utilities\ApiResponseService;

class FavoriteController extends Controller
{
    public function __construct(
        protected FavoriteService $favoriteService
    ) {}

    /* ================== Add / Remove ================== */
    public function toggle(Request $request, Apartment $apartment)
    {
        $added = $this->favoriteService->toggle(
            $request->user(),
            $apartment
        );

        return ApiResponseService::successResponse(
            data: [
                'is_favorite' => $added,
            ],
            msg: $added
                ? 'تمت الإضافة إلى المفضلة'
                : 'تمت الإزالة من المفضلة'
        );
    }

    /* ================== List ================== */
    public function index(Request $request)
    {
        $favorites = $this->favoriteService->list($request->user());

        return ApiResponseService::successResponse(
            ApartmentResource::collection($favorites)
        );
    }
}
