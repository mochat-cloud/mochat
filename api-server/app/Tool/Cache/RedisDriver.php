<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Tool\Cache;

use Hyperf\Cache\Driver\RedisDriver as BaseRedisDriver;

class RedisDriver extends BaseRedisDriver
{
    /**
     * @param string $key ...
     * @param mixed ...$value ...
     * @return bool|int ...
     */
    public function rPush(string $key, ...$value)
    {
        $vals = array_map(function ($item) {
            return $this->packer->pack($item);
        }, $value);
        return $this->redis->rPush($this->getCacheKey($key), ...$vals);
    }

    /**
     * @param string $key ...
     * @param null $default ...
     * @return bool|mixed ...
     */
    public function lPop(string $key, $default = null)
    {
        $res = $this->redis->lPop($this->getCacheKey($key));
        if ($res === false) {
            return $default;
        }

        return $this->packer->unpack($res);
    }
}
