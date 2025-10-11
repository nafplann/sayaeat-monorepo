<?php

namespace App\Services;

use SayaEat\Shared\Contracts\PyramidClientInterface;

class UserService
{
    public function __construct(
        protected PyramidClientInterface $pyramidClient
    ) {}

    public function getAll(array $filters = []): array
    {
        return $this->pyramidClient->get('internal/users', $filters);
    }

    public function getById(string $id): array
    {
        return $this->pyramidClient->get("internal/users/{$id}");
    }

    public function create(array $data): array
    {
        return $this->pyramidClient->post('internal/users', $data);
    }

    public function update(string $id, array $data): array
    {
        return $this->pyramidClient->put("internal/users/{$id}", $data);
    }

    public function delete(string $id): array
    {
        return $this->pyramidClient->delete("internal/users/{$id}");
    }
}

