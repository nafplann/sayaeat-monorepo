<?php

namespace App\Services;

use SayaEat\Shared\Contracts\PyramidClientInterface;

class RoleService
{
    public function __construct(
        protected PyramidClientInterface $pyramidClient
    ) {}

    public function getAll(array $filters = []): array
    {
        return $this->pyramidClient->get('internal/roles', $filters);
    }

    public function getById(string $id): array
    {
        return $this->pyramidClient->get("internal/roles/{$id}");
    }

    public function getPermissions(): array
    {
        return $this->pyramidClient->get('internal/roles/permissions');
    }

    public function create(array $data): array
    {
        return $this->pyramidClient->post('internal/roles', $data);
    }

    public function update(string $id, array $data): array
    {
        return $this->pyramidClient->put("internal/roles/{$id}", $data);
    }

    public function delete(string $id): array
    {
        return $this->pyramidClient->delete("internal/roles/{$id}");
    }
}

