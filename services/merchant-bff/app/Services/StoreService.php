<?php

namespace App\Services;

use SayaEat\Shared\Contracts\PyramidClientInterface;

class StoreService
{
    public function __construct(
        protected PyramidClientInterface $pyramidClient
    ) {}

    /**
     * Get all stores with filters
     */
    public function getAll(array $filters = []): array
    {
        return $this->pyramidClient->get('internal/stores', $filters);
    }

    /**
     * Get store by ID
     */
    public function getById(string $id): array
    {
        return $this->pyramidClient->get("internal/stores/{$id}");
    }

    /**
     * Create a new store
     */
    public function create(array $data): array
    {
        return $this->pyramidClient->post('internal/stores', $data);
    }

    /**
     * Update store
     */
    public function update(string $id, array $data): array
    {
        return $this->pyramidClient->put("internal/stores/{$id}", $data);
    }

    /**
     * Delete store
     */
    public function delete(string $id): array
    {
        return $this->pyramidClient->delete("internal/stores/{$id}");
    }

    /**
     * Get store products
     */
    public function getProducts(string $id): array
    {
        return $this->pyramidClient->get("internal/stores/{$id}/products");
    }

    /**
     * Toggle store status
     */
    public function toggleStatus(string $id): array
    {
        return $this->pyramidClient->post("internal/stores/{$id}/toggle-status");
    }
}

