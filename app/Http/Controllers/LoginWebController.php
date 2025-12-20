<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Enums\UserStatusEnum;
use Illuminate\Support\Facades\Auth;
use App\Models\Apartment;

class LoginWebController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'بيانات الدخول غير صحيحة',
            ]);
        }

        $user = Auth::user();

        // فقط Admin
        if (! $user->hasRole('admin')) {
            Auth::logout();
            abort(403, 'غير مصرح لك بالدخول');
        }

        return redirect()->route('admin.users.pending');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }

    

    public function index()
    {
        $users = User::where('status', UserStatusEnum::PENDING)->get();

        return view('admin.users.pending', compact('users'));
    }

    public function approve(User $user)
    {

        $user->update([
            'status' => UserStatusEnum::APPROVED,
        ]);

        return redirect()->back()->with('success', 'تمت الموافقة على المستخدم');
    }

    public function reject(User $user)
    {
        $user->update([
            'status' => UserStatusEnum::REJECTED,
        ]);

        return redirect()->back()->with('success', 'تم رفض المستخدم');
    }

    public function showImages(Apartment $apartment)
    {
        // تحميل الصور مع الشقة
        $apartment->load('images');
        return view('apartments.images', compact('apartment'));
    }
    
}
