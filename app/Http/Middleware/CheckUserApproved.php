<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserStatusEnum;
use App\Utilities\ApiResponseService;
use Illuminate\Support\Facades\Auth;

class CheckUserApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user->status !== UserStatusEnum::APPROVED) {
            return ApiResponseService::unauthorizedResponse(
                msg: 'الحساب بانتظار موافقة الإدارة'
            );
        }

        return $next($request);
    }
}
