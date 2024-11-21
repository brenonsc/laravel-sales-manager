<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Mockery;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test signup success.
     */
    public function testSignupSuccess()
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/signup', $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User registered successfully',
            ]);

        $this->assertDatabaseHas('users', ['email' => $data['email']]);
    }

    /**
     * Test signup validation error.
     */
    public function testSignupValidationError()
    {
        $data = [
            'name' => '', // Name is required
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ];

        $response = $this->postJson('/api/signup', $data);

        $response->assertStatus(422)
            ->assertJsonStructure(['error', 'messages']);
    }

    /**
     * Test login success.
     */
    public function testLoginSuccess()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User logged in successfully',
            ]);
    }

    /**
     * Test login failure.
     */
    public function testLoginFailure()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthorized',
                'message' => 'Invalid email or password.',
            ]);
    }

    /**
     * Test fetching authenticated user data.
     */
    public function testFetchAuthenticatedUser()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $response = $this->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'user']);
    }

    /**
     * Test unauthorized access to user data.
     */
    public function testFetchUserUnauthorized()
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
