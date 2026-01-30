<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\JobOrder;
use App\Models\FinishedGood;
use App\Models\ActualInventory;
use App\Models\Transfer;
use App\Models\DeliverySchedule;
use App\Models\User;
use App\Policies\ProductPolicy;
use App\Policies\JobOrderPolicy;
use App\Policies\FinishedGoodPolicy;
use App\Policies\ActualInventoryPolicy;
use App\Policies\TransferPolicy;
use App\Policies\DeliverySchedulePolicy;
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
        Transfer::class => TransferPolicy::class,
        DeliverySchedule::class => DeliverySchedulePolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}