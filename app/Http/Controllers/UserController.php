<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by department
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        $users = $query->latest()->paginate(15);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return view('users.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'department' => 'required|in:admin,sales,production,inventory,logistics',
            'profile_picture' => 'nullable|image|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $validated['password'] = Hash::make($validated['password']);

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                $validated['profile_picture'] = $request->file('profile_picture')
                    ->store('profile-pictures', 'public');
            }

            $user = User::create($validated);

            // Log activity
            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->withProperties(['new' => $user->makeHidden(['password'])->toArray()])
                ->log('User created');

            DB::commit();

            return redirect()
                ->route('users.index')
                ->with('success', 'User created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User creation failed: ' . $e->getMessage(), [
                'admin_user_id' => auth()->id()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create user. Please try again.');
        }
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load(['activityLogs' => function ($query) {
            $query->latest()->limit(20);
        }]);

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user)],
            'profile_picture' => 'nullable|image|max:2048',
        ];

        // Only admin can update these fields
        if (auth()->user()->department === 'admin') {
            $rules['username'] = ['required', 'string', 'max:255', Rule::unique('users')->ignore($user)];
            $rules['department'] = 'required|in:admin,sales,production,inventory,logistics';
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            $oldData = $user->makeHidden(['password'])->toArray();

            // Handle password update
            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                // Delete old picture
                if ($user->profile_picture) {
                    Storage::disk('public')->delete($user->profile_picture);
                }

                $validated['profile_picture'] = $request->file('profile_picture')
                    ->store('profile-pictures', 'public');
            }

            $user->update($validated);

            // Log activity
            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->withProperties(['old' => $oldData, 'new' => $user->makeHidden(['password'])->toArray()])
                ->log('User updated');

            DB::commit();

            return redirect()
                ->route('users.index')
                ->with('success', 'User updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User update failed: ' . $e->getMessage(), [
                'admin_user_id' => auth()->id(),
                'user_id' => $user->id
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update user. Please try again.');
        }
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        try {
            DB::beginTransaction();

            // Delete profile picture
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            $oldData = $user->makeHidden(['password'])->toArray();
            $user->delete();

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['old' => $oldData])
                ->log('User deleted');

            DB::commit();

            return redirect()
                ->route('users.index')
                ->with('success', 'User deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User deletion failed: ' . $e->getMessage(), [
                'admin_user_id' => auth()->id(),
                'user_id' => $user->id
            ]);

            return back()->with('error', 'Failed to delete user. Please try again.');
        }
    }

    public function profile()
    {
        $user = auth()->user();
        $user->load(['activityLogs' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return view('users.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user)],
            'profile_picture' => 'nullable|image|max:2048',
            'current_password' => 'required_with:password',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        try {
            DB::beginTransaction();

            // Verify current password if changing password
            if (!empty($validated['password'])) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return back()->withErrors(['current_password' => 'Current password is incorrect.']);
                }
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            // Handle profile picture
            if ($request->hasFile('profile_picture')) {
                if ($user->profile_picture) {
                    Storage::disk('public')->delete($user->profile_picture);
                }
                $validated['profile_picture'] = $request->file('profile_picture')
                    ->store('profile-pictures', 'public');
            }

            unset($validated['current_password']);
            $user->update($validated);

            DB::commit();

            return back()->with('success', 'Profile updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Profile update failed: ' . $e->getMessage(), [
                'user_id' => $user->id
            ]);

            return back()->with('error', 'Failed to update profile. Please try again.');
        }
    }
}