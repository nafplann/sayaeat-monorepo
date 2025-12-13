<?php

namespace App\Services;

use SayaEat\Shared\Contracts\PyramidClientInterface;

class SettingService
{
    public function __construct(
        protected PyramidClientInterface $pyramidClient
    ) {}

    public function getAll(): array
    {
        return $this->pyramidClient->get('internal/settings');
    }

    public function get(string $key): array
    {
        return $this->pyramidClient->get("internal/settings/{$key}");
    }

    public function update(array $data): array
    {
        return $this->pyramidClient->post('internal/settings', $data);
    }
}

