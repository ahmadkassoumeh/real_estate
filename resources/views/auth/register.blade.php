@extends('layouts.app')

@section('title', 'إنشاء حساب')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl p-8 w-full max-w-md animate-slide-in">
        <div class="flex items-center justify-center mb-6">
            <i class="fas fa-user-plus text-6xl text-green-500"></i>
        </div>
        
        <h2 class="text-3xl font-bold text-center mb-6 text-gray-800">إنشاء حساب</h2>
        
        <div id="error-message" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm"></div>
        
        <div class="space-y-4">
            <div>
                <input 
                    type="text" 
                    id="name" 
                    placeholder="الاسم"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                    required
                >
            </div>
            
            <div>
                <input 
                    type="email" 
                    id="email" 
                    placeholder="البريد الإلكتروني"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                    required
                >
            </div>
            
            <div>
                <input 
                    type="password" 
                    id="password" 
                    placeholder="كلمة المرور (6 أحرف على الأقل)"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                    required
                >
            </div>
            
            <div>
                <input 
                    type="password" 
                    id="password_confirmation" 
                    placeholder="تأكيد كلمة المرور"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                    required
                >
            </div>
            
            <button 
                onclick="handleRegister()" 
                id="register-btn"
                class="w-full bg-green-500 text-white py-3 rounded-lg font-semibold hover:bg-green-600 transition disabled:opacity-50"
            >
                تسجيل
            </button>
        </div>
        
        <p class="text-center mt-4 text-gray-600">
            عندك حساب؟ 
            <a href="/login" class="text-green-500 font-semibold hover:underline">
                سجل دخول
            </a>
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    if (getToken()) {
        window.location.href = '/chat';
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('password_confirmation').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                handleRegister();
            }
        });
    });

    async function handleRegister() {
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const password_confirmation = document.getElementById('password_confirmation').value;
        const btn = document.getElementById('register-btn');
        const errorDiv = document.getElementById('error-message');

        if (!name || !email || !password || !password_confirmation) {
            showError('يرجى ملء جميع الحقول');
            return;
        }

        if (password.length < 6) {
            showError('كلمة المرور يجب أن تكون 6 أحرف على الأقل');
            return;
        }

        if (password !== password_confirmation) {
            showError('كلمة المرور وتأكيد كلمة المرور غير متطابقتين');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التسجيل...';
        errorDiv.classList.add('hidden');

        try {
            const data = await apiRequest('/register', {
                method: 'POST',
                body: JSON.stringify({ name, email, password, password_confirmation })
            });

            setToken(data.token);
            window.location.href = '/chat';

        } catch (error) {
            let errorMessage = 'خطأ في التسجيل';
            
            if (error.errors) {
                errorMessage = Object.values(error.errors).flat().join('<br>');
            } else if (error.message) {
                errorMessage = error.message;
            }
            
            showError(errorMessage);
            btn.disabled = false;
            btn.innerHTML = 'تسجيل';
        }
    }

    function showError(message) {
        const errorDiv = document.getElementById('error-message');
        errorDiv.innerHTML = message;
        errorDiv.classList.remove('hidden');
    }
</script>
@endpush