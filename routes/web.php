<?php

// routes/web.php
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\JobOrderController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\FinishedGoodController;
use App\Http\Controllers\DeliveryScheduleController;
use App\Http\Controllers\ActualInventoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AIAssistantController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SequenceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public / Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', function () {
        return redirect()->route('login');
    })->name('register');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Redirect root → dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard.index');
    });

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'getNotifications'])->name('index');
        Route::post('/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    });

    /*
    |--------------------------------------------------------------------------
    | Dashboard (visible to all authenticated users)
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/',        [DashboardController::class, 'index'])     ->name('index');
        Route::get('/sales',    [DashboardController::class, 'sales'])    ->name('sales');
        Route::get('/production',[DashboardController::class, 'production'])->name('production');
        Route::get('/inventory', [DashboardController::class, 'inventory'])->name('inventory');
        Route::get('/logistics', [DashboardController::class, 'logistics'])->name('logistics');
    });

    /*
    |--------------------------------------------------------------------------
    | Master Data (usually Admin only)
    |--------------------------------------------------------------------------
    */

    // Lightweight endpoint for suggesting next identifiers (used by create forms)
    Route::get('api/sequences/next', [SequenceController::class, 'next'])->name('api.sequences.next');
    Route::middleware('role:admin')->group(function () {
        Route::resource('products',  ProductController::class);
        Route::resource('users',     UserController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Job Orders (Admin + Sales)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,sales,production')->group(function () {
        Route::resource('job-orders', JobOrderController::class);

        Route::post('job-orders/{jobOrder}/approve', [JobOrderController::class, 'approve'])
            ->name('job-orders.approve');

        Route::post('job-orders/{jobOrder}/cancel', [JobOrderController::class, 'cancel'])
            ->name('job-orders.cancel');

        Route::post('job-orders/{jobOrder}/update-status', [JobOrderController::class, 'updateStatus'])
            ->name('job-orders.update-status');

        Route::get('job-orders/{jobOrder}/details', [JobOrderController::class, 'getDetails'])
            ->name('job-orders.details');
    });

    /*
    |--------------------------------------------------------------------------
    | Transfers / Production Output (Admin + Production)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,production')->group(function () {
        Route::resource('transfers', TransferController::class);
        // → provides: index, create, store, show, edit, update, destroy
        // Requires: Job Order must be in "approved" status
    });

    /*
    |--------------------------------------------------------------------------
    | Actual Inventory Counts / Cycle Counts (Admin + Inventory)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,inventory')->group(function () {
        Route::resource('actual-inventories', ActualInventoryController::class);
        // → provides: index, create, store, show, edit, update, destroy
        // Manual physical inventory counts
    });

    /*
    |--------------------------------------------------------------------------
    | Finished Goods / Stock Records (Admin + Inventory)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,inventory')->group(function () {
        Route::resource('finished-goods', FinishedGoodController::class, [
            'only' => ['index', 'show', 'edit', 'update']
        ]);
        // → provides: index, show, edit, update (no create/store/destroy)
        // Finished goods are created AUTOMATICALLY when Transfers are recorded
        // Users can only view and adjust inventory counts
    });

    /*
    |--------------------------------------------------------------------------
    | Delivery Schedules / Shipping (Admin + Logistics)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,logistics')->group(function () {
        Route::resource('delivery-schedules', DeliveryScheduleController::class);
        // → provides: index, create, store, show, edit, update, destroy
        // Requires: Job Order must be in "approved", "in_progress", or "completed" status

        Route::post('delivery-schedules/{deliverySchedule}/mark-delivered',
            [DeliveryScheduleController::class, 'markDelivered'])
            ->name('delivery-schedules.mark-delivered');
    });

    /*
    |--------------------------------------------------------------------------
    | Audit / Activity Logs (Admin only)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {
        Route::get('activity-logs',       [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
    });

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */
    Route::prefix('reports')->name('reports.')->group(function () {
        // Job Order Reports
        Route::middleware('role:admin,sales')->group(function () {
            Route::get('/job-orders',     [ReportController::class, 'jobOrders'])    ->name('job-orders');
            Route::get('/job-orders/pdf', [ReportController::class, 'jobOrdersPdf'])->name('job-orders.pdf');
        });

        // Inventory Reports
        Route::middleware('role:admin,inventory')->group(function () {
            Route::get('/inventory',     [ReportController::class, 'inventory'])    ->name('inventory');
            Route::get('/inventory/pdf', [ReportController::class, 'inventoryPdf'])->name('inventory.pdf');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | AI Assistant
    |--------------------------------------------------------------------------
    */
    Route::prefix('ai-assistant')->name('ai-assistant.')->group(function () {
        Route::get('/', [AIAssistantController::class, 'fullscreen'])->name('index');
        Route::post('/chat', [AIAssistantController::class, 'chat'])->name('chat');
        Route::get('/history', [AIAssistantController::class, 'history'])->name('history');
        Route::delete('/history', [AIAssistantController::class, 'clearHistory'])->name('clear-history');
    });

    /*
    |--------------------------------------------------------------------------
    | Settings / User Profile (from settings.php)
    |--------------------------------------------------------------------------
    */
    Route::redirect('settings', 'settings/profile');
    Route::livewire('settings/profile', 'pages::settings.profile')->name('profile.edit');
    Route::middleware('verified')->group(function () {
        Route::livewire('settings/password', 'pages::settings.password')->name('user-password.edit');
        Route::livewire('settings/appearance', 'pages::settings.appearance')->name('appearance.edit');
        Route::livewire('settings/two-factor', 'pages::settings.two-factor')->name('two-factor.show');
    });
});