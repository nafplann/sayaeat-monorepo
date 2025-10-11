<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class MenuTest extends TestCase
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
     * Test menus datatable endpoint
     */
    public function test_menus_datatable_returns_data(): void
    {
        // Mock Pyramid API response
        Http::fake([
            '*/internal/menus*' => Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'name' => 'Nasi Goreng',
                        'merchant_id' => 1,
                        'price' => 25000,
                        'status' => 'active',
                    ],
                    [
                        'id' => 2,
                        'name' => 'Mie Goreng',
                        'merchant_id' => 1,
                        'price' => 20000,
                        'status' => 'active',
                    ],
                ],
                'total' => 2,
                'per_page' => 10,
                'current_page' => 1,
            ], 200),
        ]);

        $response = $this->get('/manage/menus/datatable?draw=1&start=0&length=10');

        $response->assertStatus(200);
        $response->assertJson([
            'recordsTotal' => 2,
            'recordsFiltered' => 2,
        ]);
        $response->assertJsonCount(2, 'data');
    }

    /**
     * Test get menus by merchant
     */
    public function test_get_menus_by_merchant(): void
    {
        // Mock Pyramid API response
        Http::fake([
            '*/internal/menus/by-merchant/1' => Http::response([
                'data' => [
                    ['id' => 1, 'name' => 'Menu 1'],
                    ['id' => 2, 'name' => 'Menu 2'],
                ],
            ], 200),
        ]);

        $response = $this->get('/manage/menus/by-merchant/1');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }

    /**
     * Test store menu with invalid data
     */
    public function test_store_menu_with_invalid_data(): void
    {
        $response = $this->post('/manage/menus', [
            'name' => '', // Required field missing
        ]);

        $response->assertSessionHasErrors(['merchant_id', 'name', 'price', 'status']);
    }

    /**
     * Test show nonexistent menu returns 404
     */
    public function test_show_nonexistent_menu_returns_404(): void
    {
        // Mock Pyramid API response for not found
        Http::fake([
            '*/internal/menus/999' => Http::response([
                'error' => 'Menu not found',
            ], 404),
        ]);

        $response = $this->get('/manage/menus/999');

        $response->assertStatus(404);
    }

    /**
     * Test update menu with invalid data
     */
    public function test_update_menu_with_invalid_data(): void
    {
        $response = $this->put('/manage/menus/1', [
            'name' => '', // Required field missing
        ]);

        $response->assertSessionHasErrors(['merchant_id', 'name', 'price', 'status']);
    }

    /**
     * Test delete menu
     */
    public function test_delete_menu(): void
    {
        // Mock Pyramid API response
        Http::fake([
            '*/internal/menus/1' => Http::response([
                'message' => 'Menu deleted successfully',
            ], 200),
        ]);

        $response = $this->delete('/manage/menus/1');

        $response->assertRedirect('/manage/menus');
        $response->assertSessionHas('success', 'Menu deleted successfully');
    }

    /**
     * Test delete nonexistent menu
     */
    public function test_delete_nonexistent_menu(): void
    {
        // Mock Pyramid API response for not found
        Http::fake([
            '*/internal/menus/999' => Http::response([
                'error' => 'Menu not found',
            ], 404),
        ]);

        $response = $this->delete('/manage/menus/999');

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test toggle menu status
     */
    public function test_toggle_menu_status(): void
    {
        // Mock Pyramid API response
        Http::fake([
            '*/internal/menus/1/toggle-status' => Http::response([
                'data' => [
                    'id' => 1,
                    'name' => 'Test Menu',
                    'status' => 'inactive',
                ],
            ], 200),
        ]);

        $response = $this->get('/manage/menus/toggle-status/1');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'menu',
        ]);
    }

    /**
     * Test menus require authentication
     */
    public function test_menus_require_authentication(): void
    {
        // Clear session to simulate unauthenticated user
        Session::flush();

        $response = $this->get('/manage/menus');

        $response->assertRedirect('/auth/login');
    }

    /**
     * Test datatable with filters
     */
    public function test_datatable_with_filters(): void
    {
        // Mock Pyramid API response with filtered results
        Http::fake([
            '*/internal/menus*' => Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'name' => 'Nasi Goreng',
                        'merchant_id' => 1,
                        'status' => 'active',
                    ],
                ],
                'total' => 1,
                'per_page' => 10,
                'current_page' => 1,
            ], 200),
        ]);

        $response = $this->get('/manage/menus/datatable?draw=1&start=0&length=10&merchant_id=1&status=active');

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
            '*/internal/menus*' => Http::response([
                'error' => 'Internal server error',
            ], 500),
        ]);

        $response = $this->get('/manage/menus/datatable?draw=1&start=0&length=10');

        $response->assertStatus(500);
        $response->assertJsonStructure(['error']);
    }
}

