<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\JobOrder;
use App\Models\FinishedGood;
use App\Models\ActualInventory;
use App\Models\DeliverySchedule;
use App\Models\Transfer;
use App\Models\EndorseToLogistic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'System Administrator',
            'username' => 'admin', 
            'email' => 'admin@cpc.com',
            'password' => Hash::make('admin123'),
            'department' => 'admin',
        ]);

        $sales = User::create([
            'name' => 'Sales Employee',
            'username' => 'sales', 
            'email' => 'sales@cpc.com',
            'password' => Hash::make('password123'),
            'department' => 'sales',
        ]);

        User::create([
            'name' => 'Production Employee',
            'username' => 'production', 
            'email' => 'production@cpc.com',
            'password' => Hash::make('password123'),
            'department' => 'production',
        ]);

        User::create([
            'name' => 'Inventory Employee',
            'username' => 'inventory', 
            'email' => 'inventory@cpc.com',
            'password' => Hash::make('password123'),
            'department' => 'inventory',
        ]);

        User::create([
            'name' => 'Logistics Employee',
            'username' => 'logistics', 
            'email' => 'logistics@cpc.com',
            'password' => Hash::make('password123'),
            'department' => 'logistics',
        ]);
    }
}