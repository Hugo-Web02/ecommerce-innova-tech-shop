<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_product_to_cart(): void
    {
        // Arrange
        $product = Product::factory()->create(['stock' => 5, 'price' => 100]);

        // Act
        $response = $this->post(route('cart.store', $product), ['quantity' => 2]);

        // Assert
        $response->assertSessionHas('cart.'.$product->id.'.quantity', 2);
    }

    public function test_authenticated_customer_can_checkout(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create(['stock' => 5, 'price' => 100]);

        // Act
        $response = $this
            ->actingAs($user)
            ->withSession([
                'cart' => [
                    $product->id => [
                        'id' => $product->id,
                        'slug' => $product->slug,
                        'name' => $product->name,
                        'price' => 100,
                        'quantity' => 1,
                        'image_path' => null,
                    ],
                ],
            ])
            ->post('/checkout');

        // Assert
        $response->assertRedirect('/checkout/success');
    }
}
