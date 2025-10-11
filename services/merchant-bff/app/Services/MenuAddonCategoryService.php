<?php

namespace App\Services;

use SayaEat\Shared\Contracts\PyramidClientInterface;

class MenuAddonCategoryService
{
    public function __construct(
        protected PyramidClientInterface $pyramidClient
    ) {}

    public function getAll(array $filters = []): array
    {
        return $this->pyramidClient->get('internal/menu-addon-categories', $filters);
    }

    public function getById(string $id): array
    {
        return $this->pyramidClient->get("internal/menu-addon-categories/{$id}");
    }

    public function create(array $data): array
    {
        return $this->pyramidClient->post('internal/menu-addon-categories', $data);
    }

    public function update(string $id, array $data): array
    {
        return $this->pyramidClient->put("internal/menu-addon-categories/{$id}", $data);
    }

    public function delete(string $id): array
    {
        return $this->pyramidClient->delete("internal/menu-addon-categories/{$id}");
    }

    public function deleteAddon(string $id): array
    {
        return $this->pyramidClient->delete("internal/menu-addon-categories/addon-delete/{$id}");
    }
}

