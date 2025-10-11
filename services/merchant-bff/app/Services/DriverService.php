<?php

namespace App\Services;

use SayaEat\Shared\Contracts\PyramidClientInterface;

class DriverService
{
    public function __construct(
        protected PyramidClientInterface $pyramidClient
    ) {}

    public function getAll(array $filters = []): array
    {
        return $this->pyramidClient->get('internal/drivers', $filters);
    }

    public function getById(string $id): array
    {
        return $this->pyramidClient->get("internal/drivers/{$id}");
    }

    public function create(array $data): array
    {
        return $this->pyramidClient->post('internal/drivers', $data);
    }

    public function update(string $id, array $data): array
    {
        return $this->pyramidClient->put("internal/drivers/{$id}", $data);
    }

    public function delete(string $id): array
    {
        return $this->pyramidClient->delete("internal/drivers/{$id}");
    }
}

