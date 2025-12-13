<?php

namespace App\Services;

use SayaEat\Shared\Contracts\PyramidClientInterface;

class CustomerService
{
    public function __construct(
        protected PyramidClientInterface $pyramidClient
    ) {}

    /**
     * Get all customers with filters
     */
    public function getAll(array $filters = []): array
    {
        return $this->pyramidClient->get('internal/customers', $filters);
    }

    /**
     * Get customer by ID
     */
    public function getById(string $id): array
    {
        return $this->pyramidClient->get("internal/customers/{$id}");
    }

    /**
     * Create a new customer
     */
    public function create(array $data): array
    {
        return $this->pyramidClient->post('internal/customers', $data);
    }

    /**
     * Update customer
     */
    public function update(string $id, array $data): array
    {
        return $this->pyramidClient->put("internal/customers/{$id}", $data);
    }

    /**
     * Delete customer
     */
    public function delete(string $id): array
    {
        return $this->pyramidClient->delete("internal/customers/{$id}");
    }

    /**
     * Get customer addresses
     */
    public function getAddresses(string $id): array
    {
        return $this->pyramidClient->get("internal/customers/{$id}/addresses");
    }

    /**
     * Get customer orders
     */
    public function getOrders(string $id): array
    {
        return $this->pyramidClient->get("internal/customers/{$id}/orders");
    }
}

