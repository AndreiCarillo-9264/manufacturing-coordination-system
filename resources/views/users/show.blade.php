@extends('layouts.app')

@section('title', 'User Details')
@section('page-icon') <i class="fas fa-user-circle"></i> @endsection
@section('page-title', 'User: ' . $user->name)
@section('page-description', 'Detailed view of user account')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">

    <!-- Header -->
    <div class="p-6 border-b bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            @if($user->profile_picture)
            <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-full object-cover">
            @else
            <div class="w-16 h-16 rounded-full bg-gray-300 flex items-center justify-center">
                <i class="fas fa-user text-white text-2xl"></i>
            </div>
            @endif
            <div>
                <h3 class="text-xl font-semibold text-gray-800">{{ $user->name }}</h3>
                <p class="text-sm text-gray-600 mt-1">@{{ $user->username }}</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-3">
            @can('update', $user)
                <a href="{{ route('users.edit', $user) }}" 
                   class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium shadow-sm transition text-sm">
                    <i class="fas fa-edit mr-1.5"></i> Edit
                </a>
            @endcan
            @can('delete', $user)
                <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium shadow-sm transition text-sm"
                            onclick="return confirm('Delete this user? This action cannot be undone.')">
                        <i class="fas fa-trash-alt mr-1.5"></i> Delete
                    </button>
                </form>
            @endcan
        </div>
    </div>

    <!-- Main Content -->
    <div class="p-6 space-y-10">

        <!-- Basic Information -->
        <div>
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Account Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Full Name</label>
                    <p class="mt-1.5 text-gray-900">{{ $user->name }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Username</label>
                    <p class="mt-1.5 text-gray-900 font-mono">{{ $user->username }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Email Address</label>
                    <p class="mt-1.5 text-gray-900">
                        <a href="mailto:{{ $user->email }}" class="text-blue-600 hover:text-blue-800">{{ $user->email }}</a>
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Department</label>
                    <p class="mt-1.5 text-gray-900 capitalize">{{ $user->department ?? 'Not assigned' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Account Status</label>
                    <p class="mt-1.5">
                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium
                            @if($user->is_active)
                                bg-green-100 text-green-800
                            @else
                                bg-red-100 text-red-800
                            @endif">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Date Joined</label>
                    <p class="mt-1.5 text-gray-900">{{ $user->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <!-- System Access -->
        <div>
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">System Access</h4>
            <div class="bg-gray-50 p-6 rounded-lg">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700">Last Login</span>
                        <span class="text-gray-900 font-medium">
                            @if($user->last_login_at)
                                {{ $user->last_login_at->diffForHumans() }}
                            @else
                                <span class="text-gray-500">Never logged in</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t">
                        <span class="text-gray-700">Last IP Address</span>
                        <span class="text-gray-900 font-mono text-sm">{{ $user->last_login_ip ?? 'Not recorded' }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
