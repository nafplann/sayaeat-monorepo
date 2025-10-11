<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class MerchantTest extends TestCase
{
    /**
     * Setup authenticated session for tests
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Simulate authenticated user
        Session::put('user_id', 1);
        Session::put('user', [
            'id' => 1,
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'role' => 'admin',
        ]);
    }

    /**
     * Test merchants datatable endpoint
     */
    public function test_merchants_datatable_returns_data(): void
    {
        // Mock Pyramid API response
        Http::fake([
            '*/internal/merchants*' => Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'name' => 'Test Merchant',
                        'slug' => 'test-merchant',
                        'category' => 'restaurant',
                        'status' => 'active',
                    ],
                    [
                        'id' => 2,
                        'name' => 'Another Merchant',
                        'slug' => 'another-merchant',
                        'category' => 'cafe',
                        'status' => 'inactive',
                    ],
                ],
                'total' => 2,
                'per_page' => 10,
                'current_page' => 1,
            ], 200),
        ]);

        $response = $this->get('/manage/merchants/datatable?draw=1&start=0&length=10');

        $response->assertStatus(200);
        $response->assertJson([
            'recordsTotal' => 2,
            'recordsFiltered' => 2,
        ]);
        $response->assertJsonCount(2, 'data');
    }

    /**
     * Test store merchant with invalid data
     */
    public function test_store_merchant_with_invalid_data(): void
    {
        $response = $this->post('/manage/merchants', [
            'name' => '', // Required field missing
            'slug' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'slug', 'category', 'status', 'phone_number']);
    }

    /**
     * Test show nonexistent merchant returns 404
     */
    public function test_show_nonexistent_merchant_returns_404(): void
    {
        // Mock Pyramid API response for not found
        Http::fake([
            '*/internal/merchants/999' => Http::response([
                'error' => 'Merchant not found',
            ], 404),
        ]);

        $response = $this->get('/manage/merchants/999');

        $response->assertStatus(404);
    }

    /**
     * Test update merchant with invalid data
     */
    public function test_update_merchant_with_invalid_data(): void
    {
        $response = $this->put('/manage/merchants/1', [
            'name' => '', // Required field missing
        ]);

        $response->assertSessionHasErrors(['name', 'slug', 'category', 'status', 'phone_number']);
    }

    /**
     * Test delete merchant
     */
    public function test_delete_merchant(): void
    {
        // Mock Pyramid API response
        Http::fake([
            '*/internal/merchants/1' => Http::response([
                'message' => 'Merchant deleted successfully',
            ], 200),
        ]);

        $response = $this->delete('/manage/merchants/1');

        $response->assertRedirect('/manage/merchants');
        $response->assertSessionHas('success', 'Merchant deleted successfully');
    }

    /**
     * Test delete nonexistent merchant
     */
    public function test_delete_nonexistent_merchant(): void
    {
        // Mock Pyramid API response for not found
        Http::fake([
            '*/internal/merchants/999' => Http::response([
                'error' => 'Merchant not found',
            ], 404),
        ]);

        $response = $this->delete('/manage/merchants/999');

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test toggle merchant status
     */
    public function test_toggle_merchant_status(): void
    {
        // Mock Pyramid API response
        Http::fake([
            '*/internal/merchants/1/toggle-status' => Http::response([
                'data' => [
                    'id' => 1,
                    'name' => 'Test Merchant',
                    'status' => 'inactive', // Toggled from active to inactive
                ],
            ], 200),
        ]);

        $response = $this->get('/manage/merchants/toggle-status/1');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'merchant',
        ]);
    }

    /**
     * Test merchants require authentication
     */
    public function test_merchants_require_authentication(): void
    {
        // Clear session to simulate unauthenticated user
        Session::flush();

        $response = $this->get('/manage/merchants');

        $response->assertRedirect('/auth/login');
    }

    /**
     * Test datatable with search filter
     */
    public function test_datatable_with_search_filter(): void
    {
        // Mock Pyramid API response with filtered results
        Http::fake([
            '*/internal/merchants*' => Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'name' => 'Searched Merchant',
                        'slug' => 'searched-merchant',
                        'category' => 'restaurant',
                        'status' => 'active',
                    ],
                ],
                'total' => 1,
                'per_page' => 10,
                'current_page' => 1,
            ], 200),
        ]);

        $response = $this->get('/manage/merchants/datatable?draw=1&start=0&length=10&search[value]=Searched');

        $response->assertStatus(200);
        $response->assertJson([
            'recordsTotal' => 1,
        ]);
    }

    /**
     * Test datatable handles API errors gracefully
     */
    public function test_datatable_handles_api_errors(): void
    {
        // Mock Pyramid API failure
        Http::fake([
            '*/internal/merchants*' => Http::response([
                'error' => 'Internal server error',
            ], 500),
        ]);

        $response = $this->get('/manage/merchants/datatable?draw=1&start=0&length=10');

        $response->assertStatus(500);
        $response->assertJsonStructure(['error']);
    }
}

