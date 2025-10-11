<?php

namespace SayaEat\Shared\Contracts;

interface PyramidClientInterface
{
    /**
     * Send GET request to Pyramid API
     */
    public function get(string $endpoint, array $params = []): array;

    /**
     * Send POST request to Pyramid API
     */
    public function post(string $endpoint, array $data = []): array;

    /**
     * Send PUT request to Pyramid API
     */
    public function put(string $endpoint, array $data = []): array;

    /**
     * Send DELETE request to Pyramid API
     */
    public function delete(string $endpoint): array;

    /**
     * Send PATCH request to Pyramid API
     */
    public function patch(string $endpoint, array $data = []): array;
}

