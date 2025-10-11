<?php

namespace App\Services;

use SayaEat\Shared\Contracts\PyramidClientInterface;

class PromotionService
{
    public function __construct(
        protected PyramidClientInterface $pyramidClient
    ) {}

    public function getAll(array $filters = []): array
    {
        return $this->pyramidClient->get('internal/promotions', $filters);
    }

    public function getById(string $id): array
    {
        return $this->pyramidClient->get("internal/promotions/{$id}");
    }

    public function create(array $data): array
    {
        return $this->pyramidClient->post('internal/promotions', $data);
    }

    public function update(string $id, array $data): array
    {
        return $this->pyramidClient->put("internal/promotions/{$id}", $data);
    }

    public function delete(string $id): array
    {
        return $this->pyramidClient->delete("internal/promotions/{$id}");
    }
}

