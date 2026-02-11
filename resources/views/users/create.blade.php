{{-- resources/views/users/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Create User')
@section('page-icon') <i class="fas fa-user-plus"></i> @endsection
@section('page-title', 'Create New User')
@section('page-description', 'Add a new system user')

@section('content')
<x-resource-form 
    :action="route('users.store')" 
    method="POST" 
    title="New User Account" 
    description="Create a new user account. Fields marked with * are required." 
    :cancel="route('users.index')" 
    submit="Create User">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- NAME --}}
        <div>
            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                Full Name <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   value="{{ old('name') }}" 
                   required
                   placeholder="Enter full name"
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
                   value="{{ old('username') }}" 
                   required
                   placeholder="Enter unique username"
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
                   value="{{ old('email') }}" 
                   required
                   placeholder="Enter email address"
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
                <option value="admin" {{ old('department') == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="production" {{ old('department') == 'production' ? 'selected' : '' }}>Production</option>
                <option value="inventory" {{ old('department') == 'inventory' ? 'selected' : '' }}>Inventory</option>
                <option value="logistics" {{ old('department') == 'logistics' ? 'selected' : '' }}>Logistics</option>
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
                Password <span class="text-red-500">*</span>
            </label>
            <input type="password" 
                   id="password" 
                   name="password" 
                   required 
                   minlength="8"
                   placeholder="Enter a strong password (minimum 8 characters)"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('password') border-red-500 ring-2 ring-red-200 @enderror">
            <p class="mt-2 text-xs text-gray-500">Password must be at least 8 characters long.</p>
            @error('password')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- PASSWORD CONFIRMATION --}}
        <div class="md:col-span-2">
            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                Confirm Password <span class="text-red-500">*</span>
            </label>
            <input type="password" 
                   id="password_confirmation" 
                   name="password_confirmation" 
                   required 
                   minlength="8"
                   placeholder="Re-enter your password"
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