<?php
declare(strict_types=1);

namespace Warrior\RateLimiter\Driver;

use RedisException;
use support\Redis as RedisClient;
use Workerman\Worker;
use Workerman\Timer;

class Redis implements DriverInterface
{
    /**
     * 构造函数
     *
     * @param Worker|null $worker
     * @param string      $connection
     *
     * @throws RedisException
     */
    public function __construct(?Worker $worker, protected string $connection)
    {
        if ($worker) {
            Timer::add(24 * 60 * 60, function () {
                $this->clearExpire();
            });
        }
        $this->clearExpire();
    }

    /**
     * increase.
     *
     * @param string $key
     * @param int    $ttl
     * @param int    $step
     *
     * @return int
     * @throws RedisException
     */
    public function increase(string $key, int $ttl = 24 * 60 * 60, int $step = 1): int
    {
        return RedisClient::connection($this->connection)->hIncrBy('rate-limiter-' . date('Y-m-d'), "$key-" . $this->getExpireTime($ttl) . '-' . $ttl, $step) ?: 0;
    }

    /**
     * getExpireTime
     *
     * @param $ttl
     *
     * @return float|int
     */
    protected function getExpireTime($ttl): float|int
    {
        return ceil(time() / $ttl) * $ttl;
    }

    /**
     * clearExpire
     *
     * @return void
     * @throws RedisException
     */
    protected function clearExpire(): void
    {
        $keys = RedisClient::connection($this->connection)->keys('rate-limiter-*');
        foreach ($keys as $key) {
            if (!str_contains($key, 'rate-limiter-' . date('Y-m-d'))) {
                $key = str_replace(config('redis.' . $this->connection . '.prefix', ''), '', $key);
                RedisClient::connection($this->connection)->del($key);
            }
        }
    }
}