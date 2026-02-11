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

        $query = User::withTrashed(); // Include deactivated (soft-deleted) users

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

            DB::commit();

            return redirect()
                ->route('users.index')
                ->with('success', 'User created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User creation failed: ' . $e->getMessage(), [
                'user_id' => auth()->id()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create user.');
        }
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user)],
            'department' => 'required|in:admin,sales,production,inventory,logistics',
            'profile_picture' => 'nullable|image|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // Handle profile picture
            if ($request->hasFile('profile_picture')) {
                if ($user->profile_picture) {
                    Storage::disk('public')->delete($user->profile_picture);
                }
                $validated['profile_picture'] = $request->file('profile_picture')
                    ->store('profile-pictures', 'public');
            }

            $user->update($validated);

            DB::commit();

            return redirect()
                ->route('users.index')
                ->with('success', 'User updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User update failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'target_user_id' => $user->id
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update user.');
        }
    }

    public function activate(Request $request, $userId)
    {
        // Retrieve user including soft-deleted ones
        $user = User::withTrashed()->findOrFail($userId);
        $this->authorize('update', $user);

        try {
            DB::beginTransaction();

            $user->restore(); // Restore soft-deleted user
            $user->is_active = true;
            $user->deactivated_by = null;
            $user->deactivated_at = null;
            $user->deactivation_remarks = null;
            $user->save();

            DB::commit();

            Log::info('User activated', [
                'user_id' => auth()->id(),
                'target_user_id' => $user->id
            ]);

            return redirect()
                ->route('users.index')
                ->with('success', 'User activated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User activation failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'target_user_id' => $userId
            ]);

            return back()->with('error', 'Failed to activate user.');
        }
    }

    public function deactivate(Request $request, $userId)
    {
        // Retrieve user including soft-deleted ones
        $user = User::withTrashed()->findOrFail($userId);
        $this->authorize('delete', $user);

        $validated = $request->validate([
            'remarks' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            // Record deactivation metadata and soft-delete
            $user->deactivation_remarks = $validated['remarks'] ?? null;
            $user->deactivated_by = auth()->id();
            $user->deactivated_at = now();
            $user->is_active = false;
            $user->save();

            // Log enriched activity before deleting 
            app(\App\Services\ActivityLogger::class)->logModel(
                'deactivated',
                $user,
                $user->getOriginal(),
                $user->getAttributes()
            );

            $user->delete(); // Soft-delete

            DB::commit();

            return redirect()
                ->route('users.index')
                ->with('success', 'User deactivated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User deactivation failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'target_user_id' => $userId
            ]);

            return back()->with('error', 'Failed to deactivate user.');
        }
    }

    public function search(Request $request)
    {
        $q = $request->q;

        $query = User::query();

        if ($q) {
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('username', 'like', "%{$q}%");
            });
        }

        $users = $query->orderBy('name')->limit(10)->get(['id', 'name', 'department']);

        return response()->json(['success' => true, 'users' => $users]);
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