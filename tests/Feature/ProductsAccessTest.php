<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class ProductsAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_can_access_products_index()
    {
        $user = User::create([
            'name' => 'Sales User',
            'email' => 'sales@test.local',
            'password' => bcrypt('password'),
            'department' => 'sales',
        ]);

        $this->actingAs($user)
            ->get(route('products.index'))
            ->assertStatus(200);
    }

    public function test_sales_cannot_access_user_management()
    {
        $user = User::create([
            'name' => 'Sales User',
            'email' => 'sales2@test.local',
            'password' => bcrypt('password'),
            'department' => 'sales',
        ]);

        $this->actingAs($user)
            ->get(route('users.index'))
            ->assertStatus(403);
    }
}
