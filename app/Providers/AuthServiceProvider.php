<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\JobOrder;
use App\Models\FinishedGood;
use App\Models\ActualInventory;
use App\Models\InventoryTransfer;
use App\Models\DeliverySchedule;
use App\Models\EndorseToLogistic;
use App\Models\User;
use App\Policies\ProductPolicy;
use App\Policies\JobOrderPolicy;
use App\Policies\FinishedGoodPolicy;
use App\Policies\ActualInventoryPolicy;
use App\Policies\InventoryTransferPolicy;
use App\Policies\DeliverySchedulePolicy;
use App\Policies\EndorseToLogisticPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        JobOrder::class => JobOrderPolicy::class,
        FinishedGood::class => FinishedGoodPolicy::class,
        ActualInventory::class => ActualInventoryPolicy::class,
        InventoryTransfer::class => InventoryTransferPolicy::class,
        DeliverySchedule::class => DeliverySchedulePolicy::class,
        EndorseToLogistic::class => EndorseToLogisticPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Allow admins to bypass policy checks
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->isAdmin() ? true : null;
        });
    }
}