<?php

namespace LarabizCMS\LaravelModelHelper\Contracts;

interface CacheGroup
{
    public function add(string $group, string $key, $ttl = null): void;

    public function get(string $group): array;

    public function pull(string $group): void;
}
