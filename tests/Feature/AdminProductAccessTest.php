<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_open_product_create_screen(): void
    {
        // Arrange
        $customer = User::factory()->create(['role' => 'customer']);

        // Act
        $response = $this->actingAs($customer)->get('/admin/products/create');

        // Assert
        $response->assertForbidden();
    }

    public function test_admin_can_open_product_create_screen(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);

        // Act
        $response = $this->actingAs($admin)->get('/admin/products/create');

        // Assert
        $response->assertOk();
    }
}
