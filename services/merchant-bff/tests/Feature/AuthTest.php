<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class AuthTest extends TestCase
{
    /**
     * Test login page loads
     */
    public function test_login_page_loads(): void
    {
        $response = $this->get('/auth/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /**
     * Test successful login with valid credentials
     */
    public function test_successful_login_with_valid_credentials(): void
    {
        // Mock Pyramid API response
        Http::fake([
            '*/internal/auth/validate-credentials' => Http::response([
                'valid' => true,
                'user' => [
                    'id' => 1,
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'role' => 'admin',
                ],
            ], 200),
        ]);

        // Attempt login
        $response = $this->post('/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
        ]);

        // Verify user data is stored in session
        $this->assertEquals(1, Session::get('user_id'));
        $this->assertNotNull(Session::get('user'));
        $this->assertEquals('Test User', Session::get('user')['name']);
    }

    /**
     * Test login failure with invalid credentials
     */
    public function test_login_failure_with_invalid_credentials(): void
    {
        // Mock Pyramid API response for invalid credentials
        Http::fake([
            '*/internal/auth/validate-credentials' => Http::response([
                'valid' => false,
                'message' => 'Invalid credentials',
            ], 401),
        ]);

        // Attempt login with wrong credentials
        $response = $this->post('/auth/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status' => false,
        ]);

        // Verify no session data is stored
        $this->assertNull(Session::get('user_id'));
        $this->assertNull(Session::get('user'));
    }

    /**
     * Test login validation errors
     */
    public function test_login_validation_errors(): void
    {
        // Test missing email
        $response = $this->post('/auth/login', [
            'password' => 'password123',
        ]);
        $response->assertSessionHasErrors(['email']);

        // Test missing password
        $response = $this->post('/auth/login', [
            'email' => 'test@example.com',
        ]);
        $response->assertSessionHasErrors(['password']);

        // Test invalid email format
        $response = $this->post('/auth/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);
        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Test successful logout
     */
    public function test_successful_logout(): void
    {
        // First, simulate a logged-in user
        Session::put('user_id', 1);
        Session::put('user', [
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Verify user is in session
        $this->assertEquals(1, Session::get('user_id'));

        // Logout
        $response = $this->get('/auth/logout');

        $response->assertRedirect('/');

        // Verify session is cleared
        $this->assertNull(Session::get('user_id'));
        $this->assertNull(Session::get('user'));
    }

    /**
     * Test accessing protected route without authentication
     */
    public function test_protected_route_requires_authentication(): void
    {
        $response = $this->get('/manage/dashboard');

        // Should redirect to login
        $response->assertRedirect('/auth/login');
    }

    /**
     * Test accessing protected route with authentication
     */
    public function test_protected_route_with_authentication(): void
    {
        // Simulate logged-in user
        Session::put('user_id', 1);
        Session::put('user', [
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'admin',
        ]);

        // Try accessing a protected route
        // Note: We only test that auth middleware allows access
        // Full dashboard functionality would require more mocking
        $response = $this->get('/manage/merchants');

        // Should not redirect to login (would be 302 if not authenticated)
        $this->assertNotEquals(302, $response->status());
    }

    /**
     * Test session persistence after login
     */
    public function test_session_persists_after_login(): void
    {
        // Mock Pyramid API response
        Http::fake([
            '*/internal/auth/validate-credentials' => Http::response([
                'valid' => true,
                'user' => [
                    'id' => 1,
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'role' => 'admin',
                ],
            ], 200),
        ]);

        // Login
        $this->post('/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Verify session data persists
        $this->assertEquals(1, Session::get('user_id'));
        $this->assertEquals('Test User', Session::get('user')['name']);
    }

    /**
     * Test Pyramid API connection failure handling
     */
    public function test_pyramid_api_connection_failure(): void
    {
        // Mock Pyramid API connection failure
        Http::fake([
            '*/internal/auth/validate-credentials' => Http::response(null, 500),
        ]);

        $response = $this->post('/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status' => false,
        ]);
    }
}

