<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utilities\ApiResponseService;
use App\Http\Resources\NotificationResource;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return ApiResponseService::successResponse(
            NotificationResource::collection($request->user()->notifications)
        );
    }

    public function unread(Request $request)
    {
        return ApiResponseService::successResponse(
            $request->user()->unreadNotifications
        );
    }

    public function markAsRead($id, Request $request)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return ApiResponseService::successResponse(
            msg: 'تم تعليم الإشعار كمقروء'
        );
    }
}
