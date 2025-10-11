<?php

namespace App\Services;

use SayaEat\Shared\Contracts\PyramidClientInterface;

class ProductDiscountService
{
    public function __construct(
        protected PyramidClientInterface $pyramidClient
    ) {}

    public function getAll(array $filters = []): array
    {
        return $this->pyramidClient->get('internal/product-discounts', $filters);
    }

    public function getById(string $id): array
    {
        return $this->pyramidClient->get("internal/product-discounts/{$id}");
    }

    public function create(array $data): array
    {
        return $this->pyramidClient->post('internal/product-discounts', $data);
    }

    public function update(string $id, array $data): array
    {
        return $this->pyramidClient->put("internal/product-discounts/{$id}", $data);
    }

    public function delete(string $id): array
    {
        return $this->pyramidClient->delete("internal/product-discounts/{$id}");
    }
}

