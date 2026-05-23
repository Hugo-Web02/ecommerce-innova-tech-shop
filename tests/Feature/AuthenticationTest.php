<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        // Arrange
        $payload = [
            'name' => 'Cliente Nuevo',
            'email' => 'cliente@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        // Act
        $this->post('/register', $payload);

        // Assert
        $this->assertAuthenticated();
    }

    public function test_user_can_login(): void
    {
        // Arrange
        $user = User::factory()->create(['password' => 'password']);

        // Act
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Assert
        $this->assertAuthenticatedAs($user);
    }
}
