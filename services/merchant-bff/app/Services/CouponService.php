<?php

namespace App\Services;

use SayaEat\Shared\Contracts\PyramidClientInterface;

class CouponService
{
    public function __construct(
        protected PyramidClientInterface $pyramidClient
    ) {}

    public function getAll(array $filters = []): array
    {
        return $this->pyramidClient->get('internal/coupons', $filters);
    }

    public function getById(string $id): array
    {
        return $this->pyramidClient->get("internal/coupons/{$id}");
    }

    public function create(array $data): array
    {
        return $this->pyramidClient->post('internal/coupons', $data);
    }

    public function update(string $id, array $data): array
    {
        return $this->pyramidClient->put("internal/coupons/{$id}", $data);
    }

    public function delete(string $id): array
    {
        return $this->pyramidClient->delete("internal/coupons/{$id}");
    }
}

