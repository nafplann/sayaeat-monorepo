<?php

namespace App\Services;

use SayaEat\Shared\Contracts\PyramidClientInterface;

class ProductService
{
    public function __construct(
        protected PyramidClientInterface $pyramidClient
    ) {}

    /**
     * Get all products with filters
     */
    public function getAll(array $filters = []): array
    {
        return $this->pyramidClient->get('internal/products', $filters);
    }

    /**
     * Get product by ID
     */
    public function getById(string $id): array
    {
        return $this->pyramidClient->get("internal/products/{$id}");
    }

    /**
     * Get products by store
     */
    public function getByStore(string $storeId): array
    {
        return $this->pyramidClient->get("internal/products/by-store/{$storeId}");
    }

    /**
     * Create a new product
     */
    public function create(array $data): array
    {
        return $this->pyramidClient->post('internal/products', $data);
    }

    /**
     * Update product
     */
    public function update(string $id, array $data): array
    {
        return $this->pyramidClient->put("internal/products/{$id}", $data);
    }

    /**
     * Delete product
     */
    public function delete(string $id): array
    {
        return $this->pyramidClient->delete("internal/products/{$id}");
    }

    /**
     * Toggle product status
     */
    public function toggleStatus(string $id): array
    {
        return $this->pyramidClient->post("internal/products/{$id}/toggle-status");
    }
}

