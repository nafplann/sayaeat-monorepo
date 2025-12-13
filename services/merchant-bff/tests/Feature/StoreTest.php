<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class StoreTest extends TestCase
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
     * Test stores datatable endpoint
     */
    public function test_stores_datatable_returns_data(): void
    {
        // Mock Pyramid API response
        Http::fake([
            '*/internal/stores*' => Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'name' => 'Store A',
                        'slug' => 'store-a',
                        'category' => 'grocery',
                        'status' => 'active',
                    ],
                    [
                        'id' => 2,
                        'name' => 'Store B',
                        'slug' => 'store-b',
                        'category' => 'pharmacy',
                        'status' => 'active',
                    ],
                ],
                'total' => 2,
                'per_page' => 10,
                'current_page' => 1,
            ], 200),
        ]);

        $response = $this->get('/manage/stores/datatable?draw=1&start=0&length=10');

        $response->assertStatus(200);
        $response->assertJson([
            'recordsTotal' => 2,
            'recordsFiltered' => 2,
        ]);
        $response->assertJsonCount(2, 'data');
    }

    /**
     * Test store store with invalid data
     */
    public function test_store_store_with_invalid_data(): void
    {
        $response = $this->post('/manage/stores', [
            'name' => '', // Required field missing
            'slug' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'slug', 'category', 'status']);
    }

    /**
     * Test show nonexistent store returns 404
     */
    public function test_show_nonexistent_store_returns_404(): void
    {
        // Mock Pyramid API response for not found
        Http::fake([
            '*/internal/stores/999' => Http::response([
                'error' => 'Store not found',
            ], 404),
        ]);

        $response = $this->get('/manage/stores/999');

        $response->assertStatus(404);
    }

    /**
     * Test update store with invalid data
     */
    public function test_update_store_with_invalid_data(): void
    {
        $response = $this->put('/manage/stores/1', [
            'name' => '', // Required field missing
        ]);

        $response->assertSessionHasErrors(['name', 'slug', 'category', 'status']);
    }

    /**
     * Test delete store
     */
    public function test_delete_store(): void
    {
        // Mock Pyramid API response
        Http::fake([
            '*/internal/stores/1' => Http::response([
                'message' => 'Store deleted successfully',
            ], 200),
        ]);

        $response = $this->delete('/manage/stores/1');

        $response->assertRedirect('/manage/stores');
        $response->assertSessionHas('success', 'Store deleted successfully');
    }

    /**
     * Test delete nonexistent store
     */
    public function test_delete_nonexistent_store(): void
    {
        // Mock Pyramid API response for not found
        Http::fake([
            '*/internal/stores/999' => Http::response([
                'error' => 'Store not found',
            ], 404),
        ]);

        $response = $this->delete('/manage/stores/999');

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test toggle store status
     */
    public function test_toggle_store_status(): void
    {
        // Mock Pyramid API response
        Http::fake([
            '*/internal/stores/1/toggle-status' => Http::response([
                'data' => [
                    'id' => 1,
                    'name' => 'Test Store',
                    'status' => 'inactive',
                ],
            ], 200),
        ]);

        $response = $this->get('/manage/stores/toggle-status/1');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'store',
        ]);
    }

    /**
     * Test stores require authentication
     */
    public function test_stores_require_authentication(): void
    {
        // Clear session to simulate unauthenticated user
        Session::flush();

        $response = $this->get('/manage/stores');

        $response->assertRedirect('/auth/login');
    }

    /**
     * Test datatable with search filter
     */
    public function test_datatable_with_search_filter(): void
    {
        // Mock Pyramid API response with filtered results
        Http::fake([
            '*/internal/stores*' => Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'name' => 'Searched Store',
                        'slug' => 'searched-store',
                        'category' => 'grocery',
                        'status' => 'active',
                    ],
                ],
                'total' => 1,
                'per_page' => 10,
                'current_page' => 1,
            ], 200),
        ]);

        $response = $this->get('/manage/stores/datatable?draw=1&start=0&length=10&search[value]=Searched');

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
            '*/internal/stores*' => Http::response([
                'error' => 'Internal server error',
            ], 500),
        ]);

        $response = $this->get('/manage/stores/datatable?draw=1&start=0&length=10');

        $response->assertStatus(500);
        $response->assertJsonStructure(['error']);
    }
}

