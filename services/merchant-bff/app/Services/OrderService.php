<?php

namespace App\Services;

use SayaEat\Shared\Contracts\PyramidClientInterface;

class OrderService
{
    public function __construct(
        protected PyramidClientInterface $pyramidClient
    ) {}

    /**
     * Get all orders with filters
     */
    public function getAll(array $filters = []): array
    {
        return $this->pyramidClient->get('internal/orders', $filters);
    }

    /**
     * Get order by ID
     */
    public function getById(string $id): array
    {
        return $this->pyramidClient->get("internal/orders/{$id}");
    }

    /**
     * Create a new order
     */
    public function create(array $data): array
    {
        return $this->pyramidClient->post('internal/orders', $data);
    }

    /**
     * Update order
     */
    public function update(string $id, array $data): array
    {
        return $this->pyramidClient->put("internal/orders/{$id}", $data);
    }

    /**
     * Delete order
     */
    public function delete(string $id): array
    {
        return $this->pyramidClient->delete("internal/orders/{$id}");
    }

    /**
     * Process order
     */
    public function process(string $id): array
    {
        return $this->pyramidClient->post("internal/orders/{$id}/process");
    }

    /**
     * Cancel order
     */
    public function cancel(string $id, string $reason = null): array
    {
        return $this->pyramidClient->post("internal/orders/{$id}/cancel", [
            'reason' => $reason
        ]);
    }

    /**
     * Reject order
     */
    public function reject(string $id, string $reason = null): array
    {
        return $this->pyramidClient->post("internal/orders/{$id}/reject", [
            'reason' => $reason
        ]);
    }
}

