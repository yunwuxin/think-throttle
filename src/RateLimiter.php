<?php

namespace yunwuxin\throttle;

use Carbon\Carbon;
use think\Cache;

class RateLimiter
{
    protected $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function tooManyAttempts($key, $maxAttempts)
    {
        if ($this->attempts($key) >= $maxAttempts) {
            if ($this->cache->has($key . ':timer')) {
                return true;
            }

            $this->resetAttempts($key);
        }

        return false;
    }

    protected function addCache($key, $value, $ttl = null)
    {
        if (!$this->cache->has($key)) {
            return $this->cache->set($key, $value, $ttl);
        }
        return false;
    }

    public function hit($key, $decaySeconds = 60)
    {
        $this->addCache($key . ':timer', Carbon::now()->addRealSeconds($decaySeconds)->getTimestamp(), $decaySeconds);

        $added = $this->addCache($key, 0, $decaySeconds);

        $hits = (int) $this->cache->inc($key);

        if (!$added && $hits == 1) {
            $this->cache->set($key, 1, $decaySeconds);
        }

        return $hits;
    }

    public function attempts($key)
    {
        return $this->cache->get($key, 0);
    }

    public function resetAttempts($key)
    {
        return $this->cache->delete($key);
    }

    public function retriesLeft($key, $maxAttempts)
    {
        $attempts = $this->attempts($key);

        return $maxAttempts - $attempts;
    }

    public function clear($key)
    {
        $this->resetAttempts($key);

        $this->cache->delete($key . ':timer');
    }

    public function availableIn($key)
    {
        return $this->cache->get($key . ':timer') - Carbon::now()->getTimestamp();
    }
}
