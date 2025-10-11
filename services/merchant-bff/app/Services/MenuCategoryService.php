<?php

namespace App\Services;

use SayaEat\Shared\Contracts\PyramidClientInterface;

class MenuCategoryService
{
    public function __construct(
        protected PyramidClientInterface $pyramidClient
    ) {}

    /**
     * Get all menu categories with filters
     */
    public function getAll(array $filters = []): array
    {
        return $this->pyramidClient->get('internal/menu-categories', $filters);
    }

    /**
     * Get menu category by ID
     */
    public function getById(string $id): array
    {
        return $this->pyramidClient->get("internal/menu-categories/{$id}");
    }

    /**
     * Get menu categories by merchant
     */
    public function getByMerchant(string $merchantId): array
    {
        return $this->pyramidClient->get("internal/menu-categories/by-merchant/{$merchantId}");
    }

    /**
     * Create a new menu category
     */
    public function create(array $data): array
    {
        return $this->pyramidClient->post('internal/menu-categories', $data);
    }

    /**
     * Update menu category
     */
    public function update(string $id, array $data): array
    {
        return $this->pyramidClient->put("internal/menu-categories/{$id}", $data);
    }

    /**
     * Delete menu category
     */
    public function delete(string $id): array
    {
        return $this->pyramidClient->delete("internal/menu-categories/{$id}");
    }
}

