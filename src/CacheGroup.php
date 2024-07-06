<?php

namespace LarabizCMS\LaravelModelHelper;

use Illuminate\Cache\CacheManager;
use Illuminate\Support\Traits\Macroable;
use LarabizCMS\LaravelModelHelper\Contracts\CacheGroup as QueriesCacheGroup;
use Psr\SimpleCache\InvalidArgumentException;

class CacheGroup implements QueriesCacheGroup
{
    use Macroable;

    protected CacheManager $cache;

    protected string $store = 'file';

    /**
     * CacheGroup constructor.
     *
     * @param CacheManager $cache The cache manager instance.
     */
    public function __construct(CacheManager $cache)
    {
        // Assign the cache manager instance to the 'cache' property
        $this->cache = $cache;
    }

    /**
     * Set the cache driver to be used.
     *
     * @param string $driver The name of the cache driver. Defaults to 'file'.
     * @return $this
     */
    public function driver(string $driver = 'file'): self
    {
        // Set the cache driver to be used for storing the cache groups.
        $this->store = $driver;

        return $this;
    }

    /**
     * Add a new key to a cache group.
     *
     * @param  string  $group  The name of the cache group.
     * @param  string  $key  The key to add to the cache group.
     * @param  mixed  $ttl  The time-to-live for the cache group. Defaults to null.
     * @return void
     * @throws InvalidArgumentException
     */
    public function add(string $group, string $key, $ttl = null): void
    {
        // Retrieve the keys from the cache group
        $keys = $this->get($group);

        // Add the new key to the cache group
        $keys[$key] = $key;

        // Store the updated cache group, with the new key and optional TTL
        $this->cache->store($this->store)->put($group, $keys, $ttl);
    }

    /**
     * Retrieve the keys from a cache group.
     *
     * @param string $group The name of the cache group.
     * @return array The keys stored in the cache group. If the cache group does not exist, an empty array is returned.
     * @throws InvalidArgumentException
     */
    public function get(string $group): array
    {
        // Retrieve the keys from the cache group
        // The default value is an empty array to handle cases where the cache group does not exist.
        return $this->cache->store($this->store)->get($group, []);
    }

    /**
     * Remove all keys from a cache group, and the group itself.
     *
     * @param string $group The name of the cache group.
     * @return void
     */
    public function pull(string $group): void
    {
        // Retrieve all keys from the cache group
        $keys = array_keys($this->get($group));

        // Remove each key from the cache
        foreach ($keys as $key) {
            $this->cache->store($this->store)->pull($key);
        }

        // Remove the cache group itself
        $this->cache->store($this->store)->pull($group);
    }
}
