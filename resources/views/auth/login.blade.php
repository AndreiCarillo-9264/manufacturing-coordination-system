<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Manufacturing ERP System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .hero-overlay {
            background: linear-gradient(to bottom, rgba(0,0,0,0.35) 0%, rgba(0,0,0,0.65) 100%);
        }
    </style>
</head>
<body class="min-h-screen bg-gray-900 flex flex-col">
    <div class="relative flex-grow flex items-center justify-center bg-cover bg-center"
         style="background-image: url('/images/company-background.jpg');">

        <div class="hero-overlay absolute inset-0"></div>

        <div class="relative z-10 text-center px-6 py-12 max-w-3xl">
            <img src="/images/system-logo.webp" alt="CPC Logo" class="mx-auto h-32 mb-6">

            <h1 class="text-5xl md:text-6xl font-bold text-white tracking-tight drop-shadow-lg">
                CPC Nexboard
            </h1>
            <p class="mt-3 text-xl md:text-2xl text-gray-200 font-medium drop-shadow">
                Manufacturing Coordination System
            </p>
        </div>
    </div>

    <div class="relative z-20 -mt-16 mx-auto w-full max-w-md px-6 pb-12">
        <div class="bg-white/95 backdrop-blur-sm p-8 rounded-xl shadow-2xl border border-amber-800/30">
            <form method="POST" action="{{ route('login') }}" x-data="{ showPassword: false }">
                @csrf

                @if(session('error'))
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded text-sm">
                    {{ session('error') }}
                </div>
                @endif

                <div class="mb-5">
                    <label class="block text-gray-800 text-sm font-semibold mb-2" for="username">
                        Username
                    </label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-600 focus:border-amber-600 @error('username') border-red-500 @enderror"
                           required autofocus>
                    @error('username')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-gray-800 text-sm font-semibold mb-2" for="password">
                        Password
                    </label>
                    <div class="relative">
                        <input 
                            :type="showPassword ? 'text' : 'password'" 
                            name="password" 
                            id="password" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-600 focus:border-amber-600 pr-16 @error('password') border-red-500 @enderror"
                            required
                        >
                        <button 
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center px-4 text-sm font-medium text-gray-600 hover:text-amber-800"
                        >
                            <span x-text="showPassword ? 'Hide' : 'Show'"></span>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-center mb-6">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="remember" id="remember"
                               class="w-5 h-5 text-amber-700 border-gray-300 rounded focus:ring-amber-600">
                        <span class="ml-3 text-gray-800 font-medium">Remember me</span>
                    </label>
                </div>

                <button type="submit" 
                        class="w-full bg-amber-800 hover:bg-amber-900 text-white font-bold py-3 px-6 rounded-lg transition duration-200 shadow-md">
                    Sign In
                </button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-amber-950 text-amber-200 text-center py-4 text-sm mt-auto">
        © 2026 Manufacturing Coordination System
    </footer>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>