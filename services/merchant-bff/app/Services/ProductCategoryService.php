<?php

namespace App\Services;

use SayaEat\Shared\Contracts\PyramidClientInterface;

class ProductCategoryService
{
    public function __construct(
        protected PyramidClientInterface $pyramidClient
    ) {}

    public function getAll(array $filters = []): array
    {
        return $this->pyramidClient->get('internal/product-categories', $filters);
    }

    public function getById(string $id): array
    {
        return $this->pyramidClient->get("internal/product-categories/{$id}");
    }

    public function getByMerchant(string $merchantId): array
    {
        return $this->pyramidClient->get("internal/product-categories/by-merchant/{$merchantId}");
    }

    public function create(array $data): array
    {
        return $this->pyramidClient->post('internal/product-categories', $data);
    }

    public function update(string $id, array $data): array
    {
        return $this->pyramidClient->put("internal/product-categories/{$id}", $data);
    }

    public function delete(string $id): array
    {
        return $this->pyramidClient->delete("internal/product-categories/{$id}");
    }
}

