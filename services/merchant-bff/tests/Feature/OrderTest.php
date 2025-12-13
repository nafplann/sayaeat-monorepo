<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class OrderTest extends TestCase
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
     * Test orders datatable endpoint
     */
    public function test_orders_datatable_returns_data(): void
    {
        // Mock Pyramid API response
        Http::fake([
            '*/internal/orders*' => Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'order_number' => 'ORD-001',
                        'customer_id' => 1,
                        'merchant_id' => 1,
                        'total' => 50000,
                        'status' => 'pending',
                    ],
                    [
                        'id' => 2,
                        'order_number' => 'ORD-002',
                        'customer_id' => 2,
                        'merchant_id' => 1,
                        'total' => 75000,
                        'status' => 'completed',
                    ],
                ],
                'total' => 2,
                'per_page' => 10,
                'current_page' => 1,
            ], 200),
        ]);

        $response = $this->get('/manage/orders/datatable?draw=1&start=0&length=10');

        $response->assertStatus(200);
        $response->assertJson([
            'recordsTotal' => 2,
            'recordsFiltered' => 2,
        ]);
        $response->assertJsonCount(2, 'data');
    }

    /**
     * Test orders list endpoint
     */
    public function test_orders_list_returns_data(): void
    {
        // Mock Pyramid API response
        Http::fake([
            '*/internal/orders*' => Http::response([
                'data' => [
                    ['id' => 1, 'order_number' => 'ORD-001', 'status' => 'pending'],
                    ['id' => 2, 'order_number' => 'ORD-002', 'status' => 'pending'],
                ],
            ], 200),
        ]);

        $response = $this->get('/manage/orders/list?status=pending');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }

    /**
     * Test show nonexistent order returns 404
     */
    public function test_show_nonexistent_order_returns_404(): void
    {
        // Mock Pyramid API response for not found
        Http::fake([
            '*/internal/orders/999' => Http::response([
                'error' => 'Order not found',
            ], 404),
        ]);

        $response = $this->get('/manage/orders/999');

        $response->assertStatus(404);
    }

    /**
     * Test process order
     */
    public function test_process_order(): void
    {
        // Mock Pyramid API response
        Http::fake([
            '*/internal/orders/1/process' => Http::response([
                'data' => [
                    'id' => 1,
                    'status' => 'processing',
                ],
            ], 200),
        ]);

        $response = $this->post('/manage/orders/process/1');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Order processed successfully',
        ]);
    }

    /**
     * Test reject order
     */
    public function test_reject_order(): void
    {
        // Mock Pyramid API response
        Http::fake([
            '*/internal/orders/1/reject' => Http::response([
                'data' => [
                    'id' => 1,
                    'status' => 'rejected',
                ],
            ], 200),
        ]);

        $response = $this->post('/manage/orders/reject/1', [
            'reason' => 'Out of stock',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Order rejected successfully',
        ]);
    }

    /**
     * Test process nonexistent order
     */
    public function test_process_nonexistent_order(): void
    {
        // Mock Pyramid API response for not found
        Http::fake([
            '*/internal/orders/999/process' => Http::response([
                'error' => 'Order not found',
            ], 404),
        ]);

        $response = $this->post('/manage/orders/process/999');

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
        ]);
    }

    /**
     * Test orders require authentication
     */
    public function test_orders_require_authentication(): void
    {
        // Clear session to simulate unauthenticated user
        Session::flush();

        $response = $this->get('/manage/orders');

        $response->assertRedirect('/auth/login');
    }

    /**
     * Test datatable with filters
     */
    public function test_datatable_with_filters(): void
    {
        // Mock Pyramid API response with filtered results
        Http::fake([
            '*/internal/orders*' => Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'order_number' => 'ORD-001',
                        'status' => 'pending',
                        'merchant_id' => 1,
                    ],
                ],
                'total' => 1,
                'per_page' => 10,
                'current_page' => 1,
            ], 200),
        ]);

        $response = $this->get('/manage/orders/datatable?draw=1&start=0&length=10&status=pending&merchant_id=1');

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
            '*/internal/orders*' => Http::response([
                'error' => 'Internal server error',
            ], 500),
        ]);

        $response = $this->get('/manage/orders/datatable?draw=1&start=0&length=10');

        $response->assertStatus(500);
        $response->assertJsonStructure(['error']);
    }

    /**
     * Test list endpoint handles API errors
     */
    public function test_list_handles_api_errors(): void
    {
        // Mock Pyramid API failure
        Http::fake([
            '*/internal/orders*' => Http::response([
                'error' => 'Internal server error',
            ], 500),
        ]);

        $response = $this->get('/manage/orders/list');

        $response->assertStatus(500);
        $response->assertJsonStructure(['error']);
    }
}

