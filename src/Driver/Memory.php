<?php
declare(strict_types=1);

namespace Warrior\RateLimiter\Driver;

use Workerman\Timer;
use Workerman\Worker;

class Memory implements DriverInterface
{
    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @var array
     */
    protected array $expire = [];

    /**
     * 构造函数
     *
     * @param Worker|null $worker
     */
    public function __construct(?Worker $worker)
    {
        if (!$worker) {
            return;
        }
        Timer::add(60, function () {
            $this->clearExpire();
        });
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
        $expireTime = $this->getExpireTime($ttl);
        $key = "$key-$expireTime-$ttl";
        if (!isset($this->data[$key])) {
            $this->data[$key] = 0;
            $this->expire[$expireTime][$key] = time();
        }
        $this->data[$key] += $step;
        return $this->data[$key];
    }

    /**
     * getExpireTime.
     *
     * @param $ttl
     *
     * @return int|float
     */
    protected function getExpireTime($ttl): int|float
    {
        return ceil(time() / $ttl) * $ttl;
    }

    /**
     * clearExpire.
     *
     * @return void
     */
    protected function clearExpire(): void
    {
        foreach ($this->expire as $expireTime => $keys) {
            if ($expireTime < time()) {
                foreach ($keys as $key => $time) {
                    unset($this->data[$key]);
                }
                unset($this->expire[$expireTime]);
            }
        }
    }
}