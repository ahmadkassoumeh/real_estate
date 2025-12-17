<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'nullable|email|unique:users,email',
            'username' => 'required|string|max:50|unique:users,username',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'email' => $request->email,
            'username' => $request->username,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'date_of_birth' => $request->date_of_birth,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('API Token')->accessToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'msg' => "تم التسجيل بنجاح ",
        ], 201);
    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'بيانات الدخول غير صحيحة'
            ], 401);
        }

        $token = $user->createToken('API Token')->accessToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح'
        ]);
    }

    // AuthController.php
    public function status(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'User is authenticated and token is valid',
            'data' => [
                'user' => [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'first_name' => $request->user()->first_name,  // إذا أضفتها
                    'last_name' => $request->user()->last_name,    // إذا أضفتها
                ],
                'token_valid' => true,
                'authenticated_at' => now()->toDateTimeString(),
                'token_expires_at' => $this->getTokenExpiration($request), // اختياري
            ]
        ], 200);
    }

    // دالة اختيارية لحساب انتهاء صلاحية التوكن
    private function getTokenExpiration(Request $request)
    {
        if (method_exists($request->user()->currentAccessToken(), 'expires_at')) {
            return $request->user()->currentAccessToken()->expires_at;
        }

        // إذا كنت تستخدم Laravel Sanctum
        if (config('passport.expiration')) {
            return now()->addMinutes(config('passport.expiration'));
        }

        return null;
    }
}
