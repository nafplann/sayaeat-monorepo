<?php

namespace App\Services;

use SayaEat\Shared\Contracts\PyramidClientInterface;

class MerchantService
{
    public function __construct(
        protected PyramidClientInterface $pyramidClient
    ) {}

    /**
     * Get all merchants with filters
     */
    public function getAll(array $filters = []): array
    {
        return $this->pyramidClient->get('internal/merchants', $filters);
    }

    /**
     * Get merchant by ID
     */
    public function getById(string $id): array
    {
        return $this->pyramidClient->get("internal/merchants/{$id}");
    }

    /**
     * Create a new merchant
     */
    public function create(array $data): array
    {
        return $this->pyramidClient->post('internal/merchants', $data);
    }

    /**
     * Update merchant
     */
    public function update(string $id, array $data): array
    {
        return $this->pyramidClient->put("internal/merchants/{$id}", $data);
    }

    /**
     * Delete merchant
     */
    public function delete(string $id): array
    {
        return $this->pyramidClient->delete("internal/merchants/{$id}");
    }

    /**
     * Get merchant menus
     */
    public function getMenus(string $id): array
    {
        return $this->pyramidClient->get("internal/merchants/{$id}/menus");
    }

    /**
     * Get merchant menu categories
     */
    public function getMenuCategories(string $id): array
    {
        return $this->pyramidClient->get("internal/merchants/{$id}/menu-categories");
    }

    /**
     * Toggle merchant status
     */
    public function toggleStatus(string $id): array
    {
        return $this->pyramidClient->post("internal/merchants/{$id}/toggle-status");
    }
}

