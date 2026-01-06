<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Chat App')</title>
    
    <!-- Tailwind CSS من CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome للأيقونات -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #10b981;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #059669;
        }

        /* Animation */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-in {
            animation: slideIn 0.3s ease-out;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-100">
    @yield('content')

    <script>
        // Base API URL
        const API_URL = 'http://localhost:8000/api';
        
        // Get Token from localStorage
        function getToken() {
            return localStorage.getItem('chat_token');
        }

        // Set Token to localStorage
        function setToken(token) {
            localStorage.setItem('chat_token', token);
        }

        // Remove Token
        function removeToken() {
            localStorage.removeItem('chat_token');
        }

        // API Request Helper
        async function apiRequest(endpoint, options = {}) {
            const token = getToken();
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...options.headers
            };

            if (token) {
                headers['Authorization'] = `Bearer ${token}`;
            }

            try {
                const response = await fetch(`${API_URL}${endpoint}`, {
                    ...options,
                    headers
                });

                const data = await response.json();

                if (!response.ok) {
                    throw data;
                }

                return data;
            } catch (error) {
                console.error('API Error:', error);
                throw error;
            }
        }
    </script>

    @stack('scripts')
</body>
</html>