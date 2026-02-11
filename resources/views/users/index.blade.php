{{-- resources/views/users/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Users')
@section('page-icon') <i class="fas fa-users"></i> @endsection
@section('page-title', 'Users')
@section('page-description', 'Manage system users and permissions')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <x-page-header title="All Users" description="User accounts and access control">
        <x-slot name="actions">
            @can('create', App\Models\User::class)
            <a href="{{ route('users.create') }}" 
               class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm hover:shadow">
                <i class="fas fa-plus mr-2"></i> Add User
            </a>
            @endcan
        </x-slot>
    </x-page-header>

    {{-- SEARCH & FILTER --}}
    <div class="p-6 bg-gradient-to-br from-gray-50 to-gray-100/50 border-b border-gray-200">
        <form method="GET" action="{{ route('users.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Search Input --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2">Search Users</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 text-sm"></i>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Name or email..."
                               class="block w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 placeholder-gray-400">
                    </div>
                </div>

                {{-- Role Filter --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2">Role</label>
                    <select name="role" class="block w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                        <option value="">All Roles</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="manager" {{ request('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                        <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                    </select>
                </div>

                {{-- Department Filter --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2">Department</label>
                    <select name="department" class="block w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                        <option value="">All Departments</option>
                        <option value="production" {{ request('department') == 'production' ? 'selected' : '' }}>Production</option>
                        <option value="warehouse" {{ request('department') == 'warehouse' ? 'selected' : '' }}>Warehouse</option>
                        <option value="logistics" {{ request('department') == 'logistics' ? 'selected' : '' }}>Logistics</option>
                        <option value="admin" {{ request('department') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-end gap-2">
                    <button type="submit" 
                            class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                        <i class="fas fa-filter mr-2 text-xs"></i>
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'role', 'department']))
                    <a href="{{ route('users.index') }}" 
                       class="inline-flex items-center justify-center px-4 py-2.5 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg border border-gray-300 transition-all duration-200"
                       title="Clear filters">
                        <i class="fas fa-times text-xs"></i>
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0 z-10">
                <tr>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Name
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Email
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Role
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Department
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Last Login
                    </th>
                    <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap sticky right-0 bg-gray-50 shadow-[-4px_0_6px_-1px_rgba(0,0,0,0.1)]">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($users as $user)
                <tr class="hover:bg-blue-50/30 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-10 w-10 flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-sm font-semibold text-blue-600">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $user->email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                        $roleColors = [
                            'admin' => 'bg-purple-100 text-purple-800',
                            'sales' => 'bg-blue-100 text-blue-800',
                            'production' => 'bg-yellow-100 text-yellow-800',
                            'inventory' => 'bg-green-100 text-green-800',
                            'logistics' => 'bg-orange-100 text-orange-800',
                        ];
                        $color = $roleColors[$user->department] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $color }}">
                            {{ ucfirst($user->department) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900 capitalize">{{ $user->department ?? '—' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">
                            {{ $user->last_login_at?->format('M d, Y h:i A') ?? 'Never' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @if($user->is_active)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Active
                        </span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Inactive
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center sticky right-0 bg-white shadow-[-4px_0_6px_-1px_rgba(0,0,0,0.08)]">
                        <div class="flex items-center justify-center gap-3">
                            @can('update', $user)
                            <a href="{{ route('users.edit', $user) }}" 
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 hover:bg-amber-50 transition-colors" 
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                            @can('delete', $user)
                                @if($user->is_active)
                                <form action="{{ route('users.deactivate', $user->id) }}" method="POST" class="inline deactivate-form" data-user-id="{{ $user->id }}">
                                    @csrf
                                    <input type="hidden" name="remarks" class="deactivate-remarks-input" value="">
                                    <button type="button" onclick="openDeactivateModal(this)" 
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-600 hover:bg-red-50 transition-colors" 
                                            title="Deactivate User">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                </form>
                                @else
                                <form action="{{ route('users.activate', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-green-600 hover:bg-green-50 transition-colors" 
                                            title="Activate User"
                                            onclick="return confirm('Are you sure you want to activate this user?')">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                </form>
                                @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-500">
                            <i class="fas fa-users text-4xl mb-3 text-gray-300"></i>
                            <p class="text-lg font-medium">No users found</p>
                            @if(request()->hasAny(['search', 'role', 'department']))
                            <p class="text-sm mt-1">Try adjusting your filters</p>
                            @else
                            <p class="text-sm mt-1">Get started by adding your first user</p>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- PAGINATION --}}
    @if($users->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        {{ $users->links() }}
    </div>
    @endif
</div>

<!-- Define deactivate modal functions before the modal is rendered -->
<script>
let _deactivateForm = null;

function openDeactivateModal(button) {
    _deactivateForm = button.closest('form');
    document.getElementById('deactivate-remarks').value = _deactivateForm.querySelector('.deactivate-remarks-input').value || '';
    document.getElementById('deactivate-modal').classList.remove('hidden');
}

function closeDeactivateModal() {
    document.getElementById('deactivate-modal').classList.add('hidden');
    _deactivateForm = null;
}

function confirmDeactivate() {
    if (!_deactivateForm) return closeDeactivateModal();
    const remarks = document.getElementById('deactivate-remarks').value || '';
    _deactivateForm.querySelector('.deactivate-remarks-input').value = remarks;
    if (confirm('Are you sure you want to deactivate this user account?')) {
        _deactivateForm.submit();
    } else {
        closeDeactivateModal();
    }
}
</script>

<!-- Deactivate modal -->
<div id="deactivate-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6">
        <h3 class="text-lg font-semibold mb-2">Deactivate User</h3>
        <p class="text-sm text-gray-600 mb-4">Provide a brief reason for deactivating this user (optional).</p>
        <textarea id="deactivate-remarks" rows="4" class="w-full border rounded-md p-2 mb-4" placeholder="Enter remarks..."></textarea>
        <div class="flex justify-end gap-2">
            <button type="button" onclick="closeDeactivateModal()" class="px-4 py-2 rounded-lg bg-white border">Cancel</button>
            <button type="button" onclick="confirmDeactivate()" class="px-4 py-2 rounded-lg bg-red-600 text-white">Deactivate</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form on Enter key
    const searchInputs = document.querySelectorAll('input[name="search"]');
    searchInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.closest('form').submit();
            }
        });
    });
});
</script>
@endsection