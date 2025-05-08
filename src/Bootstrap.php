<?php
declare(strict_types=1);

namespace Warrior\RateLimiter;

use Workerman\Worker;
use RedisException;

class Bootstrap implements \Webman\Bootstrap
{
    /**
     * start.
     *
     * @param Worker|null $worker
     *
     * @throws RedisException
     */
    public static function start(?Worker $worker): void
    {
        Limiter::init($worker);
    }
}