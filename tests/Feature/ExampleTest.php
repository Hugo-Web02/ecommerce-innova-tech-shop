<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_page_returns_successful_response(): void
    {
        // Arrange
        Product::factory()->create();

        // Act
        $response = $this->get('/');

        // Assert
        $response->assertOk();
    }
}
