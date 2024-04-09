<?php

namespace Framework\Http;

class Attributes
{
    private array $attributes = [];
    
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function all(): array
    {
        return $this->attributes;
    }

    public function has(string $key): bool
    {
        return isset($this->attributes[$key]);
    }
}
