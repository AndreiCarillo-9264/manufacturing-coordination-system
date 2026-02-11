<?php

// routes/web.php
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\JobOrderController;
use App\Http\Controllers\InventoryTransferController;
use App\Http\Controllers\FinishedGoodController;
use App\Http\Controllers\DeliveryScheduleController;
use App\Http\Controllers\EndorseToLogisticController;
use App\Http\Controllers\ActualInventoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ImportController;
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

    // User search API (used for autocomplete suggestions) — authenticated users
    Route::get('api/users/search', [UserController::class, 'search'])->name('api.users.search');

    // Products: Admin + Sales can access product masterlist
    Route::middleware('role:admin,sales')->group(function () {
        Route::resource('products', ProductController::class)->except(['show']);
        Route::post('products/import', [ImportController::class, 'products'])->name('products.import');
        Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
    });

    // Download import error files and user management (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('imports/{resource}/errors/{filename}', [ImportController::class, 'downloadErrors'])->name('imports.errors');
        Route::resource('users',     UserController::class);
        Route::post('users/{userId}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
        Route::post('users/{userId}/activate', [UserController::class, 'activate'])->name('users.activate');
    });

    /*
    |--------------------------------------------------------------------------
    | Job Orders (Admin + Sales)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,sales,production')->group(function () {
        Route::resource('job-orders', JobOrderController::class)->except(['show']);
        Route::post('job-orders/import', [ImportController::class, 'jobOrders'])->name('job-orders.import');
        Route::get('job-orders/export', [JobOrderController::class, 'export'])->name('job-orders.export');

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
    | Inventory Transfers / Production Output (Admin + Production)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,production')->group(function () {
        Route::resource('inventory-transfers', InventoryTransferController::class)->except(['show']);
        Route::post('inventory-transfers/import', [ImportController::class, 'inventoryTransfers'])->name('inventory-transfers.import');
        Route::get('inventory-transfers/export', [InventoryTransferController::class, 'export'])->name('inventory-transfers.export');
            
        // → provides: index, create, store, show, edit, update, destroy
        // Requires: Job Order must be in "approved" status
    });

    /*
    |--------------------------------------------------------------------------
    | Actual Inventory Counts / Cycle Counts (Admin + Inventory)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,inventory')->group(function () {
        Route::resource('actual-inventories', ActualInventoryController::class)->except(['show']);
        Route::post('actual-inventories/{actual_inventory}/verify', [ActualInventoryController::class, 'verify'])->name('actual-inventories.verify');
        Route::post('actual-inventories/import', [ImportController::class, 'actualInventories'])->name('actual-inventories.import');
        Route::get('actual-inventories/export', [ActualInventoryController::class, 'export'])->name('actual-inventories.export');
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
        Route::post('finished-goods/{finishedGood}/update-aging', [FinishedGoodController::class, 'updateAging'])->name('finished-goods.update-aging');
        Route::post('finished-goods/import', [ImportController::class, 'finishedGoods'])->name('finished-goods.import');
        Route::get('finished-goods/export', [FinishedGoodController::class, 'export'])->name('finished-goods.export');
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
        Route::resource('delivery-schedules', DeliveryScheduleController::class)->except(['show']);
        Route::post('delivery-schedules/import', [ImportController::class, 'deliverySchedules'])->name('delivery-schedules.import');
        Route::get('delivery-schedules/export', [DeliveryScheduleController::class, 'export'])->name('delivery-schedules.export');
        // → provides: index, create, store, show, edit, update, destroy
        // Requires: Job Order must be in "approved", "in_progress", or "completed" status

        // Endorse To Logistics (Admin + Logistics)
        Route::resource('endorse-to-logistics', EndorseToLogisticController::class)->except(['show']);
        Route::post('endorse-to-logistics/import', [ImportController::class, 'endorseToLogistics'])->name('endorse-to-logistics.import');
        Route::get('endorse-to-logistics/export', [EndorseToLogisticController::class, 'export'])->name('endorse-to-logistics.export');
        Route::post('endorse-to-logistics/{endorseToLogistic}/approve', 
            [EndorseToLogisticController::class, 'approve'])
            ->name('endorse-to-logistics.approve');
        Route::post('endorse-to-logistics/{endorseToLogistic}/dispatch', 
            [EndorseToLogisticController::class, 'dispatch'])
            ->name('endorse-to-logistics.dispatch');
        Route::post('endorse-to-logistics/{endorseToLogistic}/complete',
            [EndorseToLogisticController::class, 'complete'])
            ->name('endorse-to-logistics.complete');
        // → provides: index, create, store, show, edit, update, destroy, approve, complete

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

        // Production Reports
        Route::middleware('role:admin,production')->group(function () {
            Route::get('/production',     [ReportController::class, 'production'])    ->name('production');
            Route::get('/production/pdf', [ReportController::class, 'productionPdf'])->name('production.pdf');
        });

        // Delivery Reports
        Route::middleware('role:admin,logistics')->group(function () {
            Route::get('/deliveries',     [ReportController::class, 'deliveries'])    ->name('deliveries');
            Route::get('/deliveries/pdf', [ReportController::class, 'deliveriesPdf'])->name('deliveries.pdf');
        });

        // Logistics Reports
        Route::middleware('role:admin,logistics')->group(function () {
            Route::get('/logistics',     [ReportController::class, 'logistics'])    ->name('logistics');
            Route::get('/logistics/pdf', [ReportController::class, 'logisticsPdf'])->name('logistics.pdf');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | AI Assistant
    |--------------------------------------------------------------------------
    */
    Route::prefix('ai-assistant')->name('ai-assistant.')->group(function () {
        // Fullscreen chat page
        Route::get('/', [AIAssistantController::class, 'fullscreen'])->name('index');

        // Chat endpoint
        Route::post('/chat', [AIAssistantController::class, 'chat'])->name('chat');

        // Conversation and message management (used by floating widget and fullscreen UI)
        Route::get('/conversations', [AIAssistantController::class, 'getConversations'])->name('conversations');
        Route::get('/conversation/active', [AIAssistantController::class, 'getActiveConversation'])->name('conversation.active');
        Route::post('/conversation', [AIAssistantController::class, 'createConversation'])->name('conversation.create');
        Route::get('/conversation/{conversationId}/messages', [AIAssistantController::class, 'getConversationMessages'])->name('conversation.messages');
        Route::delete('/conversation/{conversationId}', [AIAssistantController::class, 'deleteConversation'])->name('conversation.delete');

        // Debug endpoint (local only)
        Route::get('/debug/logs', [AIAssistantController::class, 'debugLogs'])->name('debug.logs');

        // Legacy history endpoints
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