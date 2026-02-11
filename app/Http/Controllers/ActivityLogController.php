<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with(['user', 'subject'])->latest();

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by model type
        if ($request->filled('model_type')) {
            $query->where('subject_type', $request->model_type);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('event', $request->action);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activityLogs = $query->paginate(25);

        // Get unique model types for filter
        $modelTypes = ActivityLog::select('subject_type')
            ->distinct()
            ->whereNotNull('subject_type')
            ->pluck('subject_type')
            ->map(function ($type) {
                return [
                    'value' => $type,
                    'label' => class_basename($type)
                ];
            });

        // Get unique actions for filter
        $actions = ActivityLog::select('event')
            ->distinct()
            ->pluck('event');

        // Get users for filter
        $users = User::orderBy('name')->get();

        return view('activity-logs.index', compact('activityLogs', 'modelTypes', 'actions', 'users'));
    }

    public function show(ActivityLog $activityLog)
    {
        $activityLog->load(['user', 'subject']);

        return view('activity-logs.show', compact('activityLog'));
    }
    public function clear(Request $request)
    {
        $this->authorize('delete', ActivityLog::class);

        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:365'
        ]);

        try {
            $date = now()->subDays($validated['days']);
            $count = ActivityLog::where('created_at', '<', $date)->delete();


            return back()->with('success', "Successfully deleted {$count} old activity log(s).");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear activity logs.');
        }
    }
}