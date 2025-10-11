<?php

namespace App\Services;

use SayaEat\Shared\Contracts\PyramidClientInterface;

class MenuService
{
    public function __construct(
        protected PyramidClientInterface $pyramidClient
    ) {}

    /**
     * Get all menus with filters
     */
    public function getAll(array $filters = []): array
    {
        return $this->pyramidClient->get('internal/menus', $filters);
    }

    /**
     * Get menu by ID
     */
    public function getById(string $id): array
    {
        return $this->pyramidClient->get("internal/menus/{$id}");
    }

    /**
     * Get menus by merchant
     */
    public function getByMerchant(string $merchantId): array
    {
        return $this->pyramidClient->get("internal/menus/by-merchant/{$merchantId}");
    }

    /**
     * Create a new menu
     */
    public function create(array $data): array
    {
        return $this->pyramidClient->post('internal/menus', $data);
    }

    /**
     * Update menu
     */
    public function update(string $id, array $data): array
    {
        return $this->pyramidClient->put("internal/menus/{$id}", $data);
    }

    /**
     * Delete menu
     */
    public function delete(string $id): array
    {
        return $this->pyramidClient->delete("internal/menus/{$id}");
    }

    /**
     * Toggle menu status
     */
    public function toggleStatus(string $id): array
    {
        return $this->pyramidClient->post("internal/menus/{$id}/toggle-status");
    }
}

