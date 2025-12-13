<?php

namespace SayaEat\Shared\DTOs;

use JsonSerializable;

abstract class BaseDTO implements JsonSerializable
{
    /**
     * Convert DTO to array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * JSON serialize the DTO
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * Create DTO from array
     */
    public static function fromArray(array $data): static
    {
        return new static(...$data);
    }

    /**
     * Convert to JSON string
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}

