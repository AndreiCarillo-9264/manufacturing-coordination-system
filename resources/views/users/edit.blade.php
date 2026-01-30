@extends('layouts.app')

@section('title', 'Edit User')
@section('page-icon') <i class="fas fa-edit"></i> @endsection
@section('page-title', 'Edit User')
@section('page-description', 'Update user account details - ' . $user->name)

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden max-w-4xl mx-auto">
    <div class="p-6 border-b bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800">Edit User Account Form</h3>
        <p class="text-sm text-gray-600 mt-1">Update user information. Fields marked with * are required.</p>
    </div>

    <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-8">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Full Name *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                @error('name') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Username -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1.5">Username *</label>
                <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('username') border-red-500 @enderror">
                @error('username') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email Address *</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                @error('email') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Department -->
            <div>
                <label for="department" class="block text-sm font-medium text-gray-700 mb-1.5">Department *</label>
                <select name="department" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('department') border-red-500 @enderror">
                    <option value="admin" {{ old('department', $user->department) == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="sales" {{ old('department', $user->department) == 'sales' ? 'selected' : '' }}>Sales</option>
                    <option value="production" {{ old('department', $user->department) == 'production' ? 'selected' : '' }}>Production</option>
                    <option value="inventory" {{ old('department', $user->department) == 'inventory' ? 'selected' : '' }}>Inventory</option>
                    <option value="logistics" {{ old('department', $user->department) == 'logistics' ? 'selected' : '' }}>Logistics</option>
                </select>
                @error('department') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                <input type="password" name="password" minlength="8"
                       placeholder="Leave blank to keep current password" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">Leave blank to keep current password</p>
                @error('password') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Confirm Password</label>
                <input type="password" name="password_confirmation" minlength="8"
                       placeholder="Re-enter password if changing" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('password_confirmation') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Profile Picture -->
            <div class="md:col-span-2">
                <label for="profile_picture" class="block text-sm font-medium text-gray-700 mb-1.5">Profile Picture</label>
                @if($user->profile_picture)
                    <div class="mb-3">
                        <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="{{ $user->name }}" class="w-24 h-24 rounded-lg object-cover border border-gray-200">
                        <p class="mt-2 text-xs text-gray-500">Current profile picture</p>
                    </div>
                @endif
                <input type="file" name="profile_picture" accept="image/*"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('profile_picture') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">Accepted formats: JPG, PNG, GIF (Max 2MB)</p>
                @error('profile_picture') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

        </div>

        <div class="flex justify-end gap-4 pt-6 border-t">
            <a href="{{ route('users.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">Update User</button>
        </div>
    </form>
</div>
@endsection