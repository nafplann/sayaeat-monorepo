<?php

namespace App\Services;

use SayaEat\Shared\Contracts\PyramidClientInterface;

class AuditLogService
{
    public function __construct(
        protected PyramidClientInterface $pyramidClient
    ) {}

    public function getAll(array $filters = []): array
    {
        return $this->pyramidClient->get('internal/audit-logs', $filters);
    }

    public function getById(string $id): array
    {
        return $this->pyramidClient->get("internal/audit-logs/{$id}");
    }
}

