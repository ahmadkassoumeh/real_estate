<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Enums\UserStatusEnum;
use Illuminate\Support\Facades\Auth;
use App\Models\Apartment;

class LoginWebController extends Controller
{
    // في AdminController
public function index2(Request $request)
{
    $users = User::with('roles')
        ->orderBy('created_at', 'desc')
        ->paginate(15);
    
    $totalUsers = User::count();
    $ownersCount = User::role('owner')->count();
    $tenantsCount = User::role('tenant')->count();
    $activeUsers = User::where('status', 'active')->count();
    
    return view('admin.users.index', compact(
        'users', 
        'totalUsers', 
        'ownersCount', 
        'tenantsCount', 
        'activeUsers'
    ));
}

public function destroy(User $user)
{
    try {
        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('success', 'تم حذف المستخدم بنجاح.');
    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'حدث خطأ أثناء حذف المستخدم.');
    }
}
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
