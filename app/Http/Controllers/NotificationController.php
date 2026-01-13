<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utilities\ApiResponseService;
use App\Http\Resources\NotificationResource;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications;
        return ApiResponseService::successResponse(
            NotificationResource::collection($notifications)
        );
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        $user->unreadNotifications->markAsRead();

        return ApiResponseService::successResponse(
            msg: 'تم تعليم جميع الإشعارات كمقروءة'
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
