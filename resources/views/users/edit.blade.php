{{-- resources/views/users/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit User')
@section('page-icon') <i class="fas fa-edit"></i> @endsection
@section('page-title', 'Edit User: ' . $user->name)
@section('page-description', 'Update user account details')

@section('content')
<x-resource-form 
    :action="route('users.update', $user)" 
    method="PUT" 
    title="Edit User Account" 
    description="Update the user information below. Fields marked with * are required." 
    :cancel="route('users.index')" 
    submit="Update User">
    
    <x-slot name="headerRight">
        <div class="text-sm text-gray-500 font-mono bg-gray-100 px-3 py-1 rounded">
            {{ $user->username ?? $user->id }}
        </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- NAME --}}
        <div>
            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                Full Name <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   value="{{ old('name', $user->name) }}" 
                   required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('name') border-red-500 ring-2 ring-red-200 @enderror">
            @error('name')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- USERNAME --}}
        <div>
            <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">
                Username <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   id="username" 
                   name="username" 
                   value="{{ old('username', $user->username) }}" 
                   required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('username') border-red-500 ring-2 ring-red-200 @enderror">
            @error('username')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- EMAIL --}}
        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                Email Address <span class="text-red-500">*</span>
            </label>
            <input type="email" 
                   id="email" 
                   name="email" 
                   value="{{ old('email', $user->email) }}" 
                   required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('email') border-red-500 ring-2 ring-red-200 @enderror">
            @error('email')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- DEPARTMENT --}}
        <div>
            <label for="department" class="block text-sm font-semibold text-gray-700 mb-2">
                Department <span class="text-red-500">*</span>
            </label>
            <select id="department" 
                    name="department" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('department') border-red-500 ring-2 ring-red-200 @enderror">
                <option value="">Select Department</option>
                <option value="admin" {{ old('department', $user->department) == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="production" {{ old('department', $user->department) == 'production' ? 'selected' : '' }}>Production</option>
                <option value="inventory" {{ old('department', $user->department) == 'inventory' ? 'selected' : '' }}>Inventory</option>
                <option value="logistics" {{ old('department', $user->department) == 'logistics' ? 'selected' : '' }}>Logistics</option>
            </select>
            @error('department')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- PASSWORD --}}
        <div class="md:col-span-2">
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                New Password
            </label>
            <input type="password" 
                   id="password" 
                   name="password" 
                   minlength="8"
                   placeholder="Leave blank to keep current password"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('password') border-red-500 ring-2 ring-red-200 @enderror">
            <p class="mt-2 text-xs text-gray-500">Leave blank to keep current password. Minimum 8 characters if changing.</p>
            @error('password')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- PASSWORD CONFIRMATION --}}
        <div class="md:col-span-2">
            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                Confirm New Password
            </label>
            <input type="password" 
                   id="password_confirmation" 
                   name="password_confirmation" 
                   minlength="8"
                   placeholder="Re-enter new password if changing"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
            @error('password_confirmation')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

    </div>

</x-resource-form>
@endsection