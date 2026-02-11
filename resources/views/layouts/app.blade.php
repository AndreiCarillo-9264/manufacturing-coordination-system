<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Manufacturing ERP System')</title>
    @vite('resources/js/app.js')
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 antialiased">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="bg-[#f8f5f1] shadow-sm w-120 flex-shrink-0 z-30 relative">
            <div class="p-5 border-b border-gray-200">
                <h1 class="text-xl font-bold text-gray-800">CPC NEXBOARD</h1>
                <p class="text-xs text-gray-500">Manufacturing Coordination System</p>
            </div>

            <nav class="p-4 space-y-1.5 overflow-y-auto" style="max-height: calc(100vh - 140px);">
                <p class="px-4 text-xs text-gray-500 uppercase mb-3 tracking-wider">Dashboards</p>

                <a href="{{ route('dashboard.index') }}"
                   class="flex items-center px-4 py-2.5 rounded-lg transition-colors text-gray-700 hover:bg-amber-50 hover:text-amber-800 {{ request()->routeIs('dashboard.index*') ? 'bg-amber-800 text-white hover:bg-amber-900 hover:text-white font-medium' : '' }}">
                    <i class="fas fa-chart-line w-5"></i>
                    <span class="ml-3">Main Dashboard</span>
                </a>

                <a href="{{ route('dashboard.sales') }}"
                   class="flex items-center px-4 py-2.5 rounded-lg transition-colors text-gray-700 hover:bg-amber-50 hover:text-amber-800 {{ request()->routeIs('dashboard.sales*') ? 'bg-amber-800 text-white hover:bg-amber-900 hover:text-white font-medium' : '' }}">
                    <i class="fas fa-shopping-cart w-5"></i>
                    <span class="ml-3">Sales Dashboard</span>
                </a>

                <a href="{{ route('dashboard.production') }}"
                   class="flex items-center px-4 py-2.5 rounded-lg transition-colors text-gray-700 hover:bg-amber-50 hover:text-amber-800 {{ request()->routeIs('dashboard.production*') ? 'bg-amber-800 text-white hover:bg-amber-900 hover:text-white font-medium' : '' }}">
                    <i class="fas fa-industry w-5"></i>
                    <span class="ml-3">Production Dashboard</span>
                </a>

                <a href="{{ route('dashboard.inventory') }}"
                   class="flex items-center px-4 py-2.5 rounded-lg transition-colors text-gray-700 hover:bg-amber-50 hover:text-amber-800 {{ request()->routeIs('dashboard.inventory*') ? 'bg-amber-800 text-white hover:bg-amber-900 hover:text-white font-medium' : '' }}">
                    <i class="fas fa-boxes w-5"></i>
                    <span class="ml-3">Inventory Dashboard</span>
                </a>

                <a href="{{ route('dashboard.logistics') }}"
                   class="flex items-center px-4 py-2.5 rounded-lg transition-colors text-gray-700 hover:bg-amber-50 hover:text-amber-800 {{ request()->routeIs('dashboard.logistics*') ? 'bg-amber-800 text-white hover:bg-amber-900 hover:text-white font-medium' : '' }}">
                    <i class="fas fa-truck w-5"></i>
                    <span class="ml-3">Logistics Dashboard</span>
                </a>

                <div class="pt-3 mt-1 space-y-1.5 border-t border-gray-200">
                    <p class="px-4 text-xs text-gray-500 uppercase mb-3 tracking-wider">Data Management</p>

                    @if(auth()->user()->isAdmin() || auth()->user()->isSales())
                    <a href="{{ route('products.index') }}"
                       class="flex items-center px-4 py-2.5 rounded-lg transition-colors text-gray-700 hover:bg-amber-50 hover:text-amber-800 {{ request()->routeIs('products*') ? 'bg-amber-800 text-white hover:bg-amber-900 hover:text-white font-medium' : '' }}">
                        <i class="fas fa-box w-5"></i>
                        <span class="ml-3">Product Masterlist</span>
                    </a>
                    @endif

                    @if(auth()->user()->isAdmin() || auth()->user()->isSales() || auth()->user()->isProduction())
                    <a href="{{ route('job-orders.index') }}"
                       class="flex items-center px-4 py-2.5 rounded-lg transition-colors text-gray-700 hover:bg-amber-50 hover:text-amber-800 {{ request()->routeIs('job-orders*') ? 'bg-amber-800 text-white hover:bg-amber-900 hover:text-white font-medium' : '' }}">
                        <i class="fas fa-clipboard-list w-5"></i>
                        <span class="ml-3">Job Orders</span>
                    </a>
                    @endif

                    @if(auth()->user()->isAdmin() || auth()->user()->isProduction())
                    <a href="{{ route('inventory-transfers.index') }}"
                       class="flex items-center px-4 py-2.5 rounded-lg transition-colors text-gray-700 hover:bg-amber-50 hover:text-amber-800 {{ request()->routeIs('inventory-transfers*') ? 'bg-amber-800 text-white hover:bg-amber-900 hover:text-white font-medium' : '' }}">
                        <i class="fas fa-exchange-alt w-5"></i>
                        <span class="ml-3">Inventory Transfers</span>
                    </a>
                    @endif

                    @if(auth()->user()->isAdmin() || auth()->user()->isInventory())
                    <a href="{{ route('finished-goods.index') }}"
                       class="flex items-center px-4 py-2.5 rounded-lg transition-colors text-gray-700 hover:bg-amber-50 hover:text-amber-800 {{ request()->routeIs('finished-goods*') ? 'bg-amber-800 text-white hover:bg-amber-900 hover:text-white font-medium' : '' }}">
                        <i class="fas fa-check-square w-5"></i>
                        <span class="ml-3">Finished Goods</span>
                    </a>
                    @endif

                    @if(auth()->user()->isAdmin() || auth()->user()->isInventory())
                    <a href="{{ route('actual-inventories.index') }}"
                       class="flex items-center px-4 py-2.5 rounded-lg transition-colors text-gray-700 hover:bg-amber-50 hover:text-amber-800 {{ request()->routeIs('actual-inventories*') ? 'bg-amber-800 text-white hover:bg-amber-900 hover:text-white font-medium' : '' }}">
                        <i class="fas fa-boxes w-5"></i>
                        <span class="ml-3">Actual Inventory</span>
                    </a>
                    @endif

                    @if(auth()->user()->isAdmin() || auth()->user()->isLogistics() || auth()->user()->isSales())
                    <a href="{{ route('delivery-schedules.index') }}"
                       class="flex items-center px-4 py-2.5 rounded-lg transition-colors text-gray-700 hover:bg-amber-50 hover:text-amber-800 {{ request()->routeIs('delivery-schedules*') ? 'bg-amber-800 text-white hover:bg-amber-900 hover:text-white font-medium' : '' }}">
                        <i class="fas fa-truck-loading w-5"></i>
                        <span class="ml-3">Delivery Schedules</span>
                    </a>
                    @endif

                    @if(auth()->user()->isAdmin() || auth()->user()->isLogistics())
                    <a href="{{ route('endorse-to-logistics.index') }}"
                       class="flex items-center px-4 py-2.5 rounded-lg transition-colors text-gray-700 hover:bg-amber-50 hover:text-amber-800 {{ request()->routeIs('endorse-to-logistics*') ? 'bg-amber-800 text-white hover:bg-amber-900 hover:text-white font-medium' : '' }}">
                        <i class="fas fa-shipping-fast w-5"></i>
                        <span class="ml-3">Endorse To Logistics</span>
                    </a>
                    @endif

                    @if(auth()->user()->isAdmin())
                    <div class="pt-3 mt-1 space-y-1.5 border-t border-gray-200">
                        <p class="px-4 text-xs text-gray-500 uppercase mb-3 tracking-wider">Admin Control</p>

                        <a href="{{ route('users.index') }}"
                           class="flex items-center px-4 py-2.5 rounded-lg transition-colors text-gray-700 hover:bg-amber-50 hover:text-amber-800 {{ request()->routeIs('users*') ? 'bg-amber-800 text-white hover:bg-amber-900 hover:text-white font-medium' : '' }}">
                            <i class="fas fa-users w-5"></i>
                            <span class="ml-3">User Management</span>
                        </a>

                        <a href="{{ route('activity-logs.index') }}"
                           class="flex items-center px-4 py-2.5 rounded-lg transition-colors text-gray-700 hover:bg-amber-50 hover:text-amber-800 {{ request()->routeIs('activity-logs*') ? 'bg-amber-800 text-white hover:bg-amber-900 hover:text-white font-medium' : '' }}">
                            <i class="fas fa-history w-5"></i>
                            <span class="ml-3">Activity Logs</span>
                        </a>
                    </div>
                    @endif
                </div>

                <div class="pt-3 mt-1 space-y-1.5 border-t border-gray-200">
                    <p class="px-4 text-xs text-gray-500 uppercase mb-3 tracking-wider">Extra Feature</p>

                    <a href="{{ route('ai-assistant.index') }}" 
                       class="flex items-center px-4 py-2.5 rounded-lg transition-colors text-gray-700 hover:bg-amber-50 hover:text-amber-800 {{ request()->routeIs('ai-assistant*') ? 'bg-amber-800 text-white hover:bg-amber-900 hover:text-white font-medium' : '' }}">
                        <i class="fas fa-robot w-5"></i>
                        <span class="ml-3">AI Assistant</span>
                    </a>
                </div>
            </nav>

            <!-- Logout at bottom -->
            <div class="absolute bottom-0 left-0 right-0 p-2 border-t border-gray-200 bg-[#f8f5f1]">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center w-full px-4 py-2.5 rounded-lg hover:bg-red-50 text-gray-700 hover:text-red-700 transition-colors">
                        <i class="fas fa-sign-out-alt w-5"></i>
                        <span class="ml-3">Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm z-20">
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center gap-5">
                        <div class="text-4xl">
                            @yield('page-icon')
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">@yield('page-title')</h2>
                            <p class="text-sm text-gray-600">@yield('page-description')</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-5">
                        <!-- Notification Bell -->
                        <div class="relative" x-data="notificationDropdown()" x-init="init()">
                            <button @click="toggleDropdown()" class="relative p-2.5 text-gray-600 hover:bg-gray-100 rounded-full transition">
                                <i class="fas fa-bell text-xl"></i>
                                <span x-show="unreadCount > 0"
                                      x-text="unreadCount"
                                      class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold ring-2 ring-white">
                                </span>
                            </button>

                            <div x-show="isOpen"
                                 @click.outside="isOpen = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 translate-y-1"
                                 class="absolute right-0 mt-3 w-80 bg-white rounded-xl shadow-2xl z-50 border border-gray-200 overflow-hidden">

                                <div class="p-4 border-b bg-gray-50">
                                    <div class="flex justify-between items-center">
                                        <h3 class="font-semibold text-gray-800">Notifications</h3>
                                        <button @click="isOpen = false" class="text-gray-500 hover:text-gray-700">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
                                    <template x-for="notification in notifications" :key="notification.id">
                                        <a :href="notification.link" @click="markAsRead(notification.database_id); isOpen = false" class="block p-4 hover:bg-gray-50 transition cursor-pointer">
                                            <div class="flex items-start space-x-4">
                                                <div :class="{
                                                    'bg-yellow-100 text-yellow-600': notification.type === 'warning',
                                                    'bg-blue-100 text-blue-600': notification.type === 'info',
                                                    'bg-green-100 text-green-600': notification.type === 'success',
                                                    'bg-red-100 text-red-600': notification.type === 'danger'
                                                }" class="p-3 rounded-full flex-shrink-0">
                                                    <i :class="'fas text-lg ' + notification.icon"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-semibold text-gray-900" x-text="notification.title"></p>
                                                    <p class="text-sm text-gray-600 mt-1" x-text="notification.message"></p>
                                                    <p class="text-xs text-gray-400 mt-2" x-text="notification.time"></p>
                                                </div>
                                            </div>
                                        </a>
                                    </template>

                                    <div x-show="notifications.length === 0" class="p-10 text-center text-gray-500">
                                        <i class="fas fa-bell-slash text-4xl mb-3 opacity-70"></i>
                                        <p class="text-sm">No new notifications</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            @if(auth()->user()->profile_photo_path)
                            <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}" 
                                 alt="{{ auth()->user()->name }}" 
                                 class="w-10 h-10 rounded-full object-cover border border-gray-200"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=3b82f6&color=fff&size=128';">
                            @else
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-sm">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            @endif
                            <div class="hidden sm:block">
                                <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500 capitalize">{{ auth()->user()->department }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
                @if(session('success'))
                    <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r shadow-sm">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r shadow-sm">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r shadow-sm">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle mr-2 mt-0.5"></i>
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function notificationDropdown() {
            return {
                isOpen: false,
                notifications: [],
                unreadCount: 0,

                init() {
                    this.fetchNotifications();
                    // Refresh notifications every 30 seconds
                    setInterval(() => this.fetchNotifications(), 30000);
                    // Listen for real-time notifications
                    this.setupEcho();
                },

                fetchNotifications() {
                    fetch('/notifications')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.notifications = data.notifications || [];
                                this.unreadCount = data.unreadCount || 0;
                            }
                        })
                        .catch(error => console.error('Failed to fetch notifications:', error));
                },

                setupEcho() {
                    // Listen for new notifications from Laravel Echo
                    if (typeof window.Echo !== 'undefined' && window.Echo) {
                        const userId = @json(auth()->user()?->id);
                        window.Echo.private(`App.Models.User.${userId}`)
                            .notification((notification) => {
                                console.log('New notification received:', notification);
                                this.fetchNotifications();
                            });
                    }
                },

                toggleDropdown() {
                    this.isOpen = !this.isOpen;
                },

                markAsRead(notificationId) {
                    if (!notificationId) return;
                    
                    fetch('/notifications/mark-as-read', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ id: notificationId })
                    })
                    .then(() => this.fetchNotifications())
                    .catch(error => console.error('Failed to mark notification as read:', error));
                },

                markAllAsRead() {
                    fetch('/notifications/mark-all-as-read', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(() => this.fetchNotifications())
                    .catch(error => console.error('Failed to mark all as read:', error));
                }
            }
        }
    </script>
    <script src="{{ asset('js/ai-assistant-enhanced.js') }}"></script>
    @stack('scripts')
</body>
</html>