<?php

namespace App\Cache;

use DateInterval;
use DateTimeInterface;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\CacheInterface;

/**
 * PSR-16 bridge used by SimplePie to store etag / modified headers.
 *
 * It intentionally writes to a dedicated cache store: SimplePie may call
 * clear() to drop its own cache, and that must not flush the whole
 * application cache (settings, favicons, sessions).
 */
class SimpleCacheBridge implements CacheInterface
{
    /**
     * Name of the cache store that holds SimplePie's data.
     */
    public const STORE = 'simplepie';

    protected function store(): \Illuminate\Contracts\Cache\Repository
    {
        return Cache::store(self::STORE);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->store()->get($key, $default);
    }

    public function set(string $key, mixed $value, DateInterval|int|null $ttl = null): bool
    {
        $this->store()->put($key, $value, $this->ttlToMinutes($ttl));

        return true;
    }

    public function delete(string $key): bool
    {
        return $this->store()->forget($key);
    }

    public function clear(): bool
    {
        return $this->store()->flush();
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $values = [];

        foreach ($keys as $key) {
            $values[$key] = $this->get($key, $default);
        }

        return $values;
    }

    public function setMultiple(iterable $values, DateInterval|int|null $ttl = null): bool
    {
        $minutes = $this->ttlToMinutes($ttl);

        foreach ($values as $key => $value) {
            $this->store()->put($key, $value, $minutes);
        }

        return true;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    public function has(string $key): bool
    {
        return $this->store()->has($key);
    }

    /**
     * Convert a PSR-16 TTL to the number of minutes Laravel expects.
     *
     * Laravel treats a null TTL as "store forever" and an integer as minutes,
     * while PSR-16 defines an integer as seconds.
     */
    protected function ttlToMinutes(DateInterval|int|null $ttl): ?int
    {
        if (is_null($ttl)) {
            return null;
        }

        if ($ttl instanceof DateInterval) {
            $reference = new \DateTimeImmutable();
            $seconds = $reference->add($ttl)->getTimestamp() - $reference->getTimestamp();
        } else {
            $seconds = $ttl;
        }

        return (int) max(1, ceil($seconds / 60));
    }
}
