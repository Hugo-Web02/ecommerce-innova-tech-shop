<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_belongs_to_category(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        // Act
        $productCategory = $product->category;

        // Assert
        $this->assertTrue($productCategory->is($category));
    }

    public function test_product_uses_slug_for_route_binding(): void
    {
        // Arrange
        $product = new Product();

        // Act
        $routeKey = $product->getRouteKeyName();

        // Assert
        $this->assertSame('slug', $routeKey);
    }
}
