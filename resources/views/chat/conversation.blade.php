@extends('layouts.app')

@section('title', 'محادثة')

@section('content')
<div class="min-h-screen bg-gray-100 flex flex-col">
    <!-- Chat Header -->
    <div class="bg-green-600 text-white p-4 shadow-lg">
        <div class="max-w-4xl mx-auto flex items-center gap-4">
            <a href="/chat" class="hover:bg-green-700 p-2 rounded-full transition">
                <i class="fas fa-arrow-right text-xl"></i>
            </a>
            <div id="user-avatar" class="w-10 h-10 rounded-full bg-white text-green-600 flex items-center justify-center font-bold text-lg">
                ?
            </div>
            <div>
                <h2 class="font-bold text-lg" id="chat-user-name">جاري التحميل...</h2>
                <p class="text-sm text-green-100" id="chat-user-email"></p>
            </div>
        </div>
    </div>

    <!-- Messages Container -->
    <div id="messages-container" class="flex-1 overflow-y-auto p-4 max-w-4xl w-full mx-auto">
        <div id="messages-loading" class="flex items-center justify-center h-full text-gray-400">
            <div class="text-center">
                <i class="fas fa-spinner fa-spin text-4xl mb-2 opacity-50"></i>
                <p>جاري تحميل المحادثة...</p>
            </div>
        </div>

        <div id="messages-empty" class="hidden flex items-center justify-center h-full text-gray-400">
            <div class="text-center">
                <i class="fas fa-comments text-6xl mb-2 opacity-50"></i>
                <p>ابدأ المحادثة...</p>
            </div>
        </div>

        <div id="messages-list" class="hidden"></div>
    </div>

    <!-- Message Input -->
    <div class="bg-white border-t border-gray-200 p-4">
        <div class="max-w-4xl mx-auto flex gap-2">
            <input 
                type="text" 
                id="message-input"
                placeholder="اكتب رسالة..."
                class="flex-1 px-4 py-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-green-500"
            >
            <button 
                onclick="sendMessage()"
                id="send-btn"
                class="bg-green-500 text-white px-6 py-3 rounded-full hover:bg-green-600 transition disabled:opacity-50"
            >
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    if (!getToken()) {
        window.location.href = '/login';
    }

    const urlParams = new URLSearchParams(window.location.search);
    const chatUserId = window.location.pathname.split('/').pop();
    const chatUserName = urlParams.get('name') || 'مستخدم';
    const chatUserEmail = urlParams.get('email') || '';

    let currentUser = null;
    let messages = [];
    let isLoadingMessages = false;

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('chat-user-name').textContent = chatUserName;
        document.getElementById('chat-user-email').textContent = chatUserEmail;
        document.getElementById('user-avatar').textContent = chatUserName.charAt(0).toUpperCase();

        loadCurrentUser();
        loadMessages();

        setInterval(loadMessages, 3000);

        document.getElementById('message-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    });

    async function loadCurrentUser() {
        try {
            currentUser = await apiRequest('/user');
        } catch (error) {
            console.error('Error loading user:', error);
            removeToken();
            window.location.href = '/login';
        }
    }

    async function loadMessages() {
        if (isLoadingMessages) return;
        isLoadingMessages = true;

        try {
            const data = await apiRequest(`/messages/${chatUserId}`);
            
            const loadingDiv = document.getElementById('messages-loading');
            const emptyDiv = document.getElementById('messages-empty');
            const listDiv = document.getElementById('messages-list');

            loadingDiv.classList.add('hidden');

            if (data.length === 0) {
                emptyDiv.classList.remove('hidden');
                listDiv.classList.add('hidden');
            } else {
                emptyDiv.classList.add('hidden');
                listDiv.classList.remove('hidden');
                
                if (JSON.stringify(messages) !== JSON.stringify(data)) {
                    messages = data;
                    renderMessages();
                    scrollToBottom();
                }
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        } finally {
            isLoadingMessages = false;
        }
    }

    function renderMessages() {
        const listDiv = document.getElementById('messages-list');
        
        listDiv.innerHTML = messages.map(msg => {
            const isMine = msg.sender_id === (currentUser ? currentUser.id : null);
            const time = new Date(msg.created_at).toLocaleTimeString('ar-SY', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });

            return `
                <div class="mb-4 flex ${isMine ? 'justify-end' : 'justify-start'} animate-slide-in">
                    <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${
                        isMine 
                            ? 'bg-green-500 text-white rounded-br-none' 
                            : 'bg-white text-gray-800 rounded-bl-none shadow'
                    }">
                        <p class="break-words">${escapeHtml(msg.message)}</p>
                        <p class="text-xs mt-1 ${isMine ? 'text-green-100' : 'text-gray-400'}">
                            ${time}
                        </p>
                    </div>
                </div>
            `;
        }).join('');
    }

    async function sendMessage() {
        const input = document.getElementById('message-input');
        const message = input.value.trim();
        const btn = document.getElementById('send-btn');

        if (!message) return;

        input.disabled = true;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        try {
            await apiRequest('/messages/send', {
                method: 'POST',
                body: JSON.stringify({
                    receiver_id: chatUserId,
                    message: message
                })
            });

            input.value = '';
            await loadMessages();

        } catch (error) {
            console.error('Error sending message:', error);
            alert('فشل إرسال الرسالة');
        } finally {
            input.disabled = false;
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            input.focus();
        }
    }

    function scrollToBottom() {
        const container = document.getElementById('messages-container');
        container.scrollTop = container.scrollHeight;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
@endpush