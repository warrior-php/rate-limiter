<?php
declare(strict_types=1);

namespace Warrior\RateLimiter\Driver;

use support\exception\BusinessException;
use Workerman\Worker;

class Apcu implements DriverInterface
{
    /**
     * 构造函数
     *
     * @param Worker|null $worker
     */
    public function __construct(?Worker $worker)
    {
        if (!extension_loaded('apcu')) {
            throw new BusinessException('APCu extension is not loaded');
        }

        if (!apcu_enabled()) {
            throw new BusinessException('APCu is not enabled. Please set apc.enable_cli=1 in php.ini to enable APCu.');
        }
    }

    /**
     * increase.
     *
     * @param string $key
     * @param int    $ttl
     * @param int    $step
     *
     * @return int
     */
    public function increase(string $key, int $ttl = 24 * 60 * 60, int $step = 1): int
    {
        return apcu_inc("$key-" . $this->getExpireTime($ttl) . '-' . $ttl, $step, $success, $ttl) ?: 0;
    }

    /**
     * getExpireTime.
     *
     * @param $ttl
     *
     * @return float|int
     */
    protected function getExpireTime($ttl): float|int
    {
        return ceil(time() / $ttl) * $ttl;
    }
}