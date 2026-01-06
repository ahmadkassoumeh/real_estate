@extends('layouts.app')

@section('title', 'المحادثات')

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-green-600 text-white p-4 shadow-lg">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fas fa-comments text-3xl"></i>
                <div>
                    <h1 class="text-xl font-bold">المحادثات</h1>
                    <p class="text-sm text-green-100" id="current-user-name">جاري التحميل...</p>
                </div>
            </div>
            <button 
                onclick="handleLogout()" 
                class="flex items-center gap-2 bg-green-700 px-4 py-2 rounded-lg hover:bg-green-800 transition"
            >
                <i class="fas fa-sign-out-alt"></i>
                خروج
            </button>
        </div>
    </div>

    <!-- Users List -->
    <div class="max-w-4xl mx-auto bg-white shadow-lg mt-4 rounded-lg overflow-hidden">
        <div id="users-loading" class="p-8 text-center">
            <i class="fas fa-spinner fa-spin text-4xl text-green-500 mb-4"></i>
            <p class="text-gray-600">جاري تحميل المستخدمين...</p>
        </div>

        <div id="users-empty" class="hidden p-8 text-center text-gray-500">
            <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
            <p class="text-lg">ما في مستخدمين للعرض</p>
        </div>

        <div id="users-list" class="hidden"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentUser = null;

    if (!getToken()) {
        window.location.href = '/login';
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadCurrentUser();
        loadUsers();
        
        // تحديث القائمة كل 5 ثواني لعرض الرسائل الجديدة
        setInterval(loadUsers, 5000);
    });

    async function loadCurrentUser() {
        try {
            currentUser = await apiRequest('/user');
            document.getElementById('current-user-name').textContent = currentUser.name;
        } catch (error) {
            console.error('Error loading user:', error);
            removeToken();
            window.location.href = '/login';
        }
    }

    async function loadUsers() {
        try {
            const users = await apiRequest('/users');
            
            const loadingDiv = document.getElementById('users-loading');
            const emptyDiv = document.getElementById('users-empty');
            const listDiv = document.getElementById('users-list');

            loadingDiv.classList.add('hidden');

            if (users.length === 0) {
                emptyDiv.classList.remove('hidden');
            } else {
                listDiv.classList.remove('hidden');
                listDiv.innerHTML = users.map(user => `
                    <div 
                        onclick="openChat(${user.id}, '${user.name.replace(/'/g, "\\'")}', '${user.email}')" 
                        class="flex items-center gap-4 p-4 border-b border-gray-200 hover:bg-gray-50 cursor-pointer transition animate-slide-in relative"
                    >
                        <!-- Avatar -->
                        <div class="relative">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center text-white font-bold text-lg">
                                ${user.name.charAt(0).toUpperCase()}
                            </div>
                            
                            <!-- نقطة برتقالية للرسائل غير المقروءة -->
                            ${user.unread_count > 0 ? `
                                <span class="absolute -top-1 -right-1 w-5 h-5 bg-orange-500 rounded-full flex items-center justify-center text-white text-xs font-bold animate-pulse">
                                    ${user.unread_count > 9 ? '9+' : user.unread_count}
                                </span>
                            ` : ''}
                        </div>
                        
                        <!-- User Info -->
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-gray-800">${user.name}</h3>
                                ${user.unread_count > 0 ? `
                                    <span class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></span>
                                ` : ''}
                            </div>
                            <p class="text-sm text-gray-500">${user.email}</p>
                        </div>
                        
                        <!-- Arrow -->
                        <div class="text-gray-400">
                            <i class="fas fa-chevron-left"></i>
                        </div>
                    </div>
                `).join('');
            }
        } catch (error) {
            console.error('Error loading users:', error);
            const loadingDiv = document.getElementById('users-loading');
            if (loadingDiv) {
                loadingDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle text-4xl text-red-500 mb-4"></i>
                    <p class="text-red-600">فشل تحميل المستخدمين</p>
                `;
            }
        }
    }

    function openChat(userId, userName, userEmail) {
        window.location.href = `/chat/${userId}?name=${encodeURIComponent(userName)}&email=${encodeURIComponent(userEmail)}`;
    }

    async function handleLogout() {
        if (!confirm('هل تريد تسجيل الخروج؟')) {
            return;
        }

        try {
            await apiRequest('/logout', { method: 'POST' });
        } catch (error) {
            console.error('Logout error:', error);
        }

        removeToken();
        window.location.href = '/login';
    }
</script>
@endpush