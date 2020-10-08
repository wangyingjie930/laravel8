<?php
/**
 * RedisDelayHelper.php
 * @author   wangyingjie <930055912@qq.com>
 * @date     2020/9/27
 * PhpStorm
 * @desc:
 */

namespace App\Http\Components;


use Illuminate\Support\Facades\Redis;

class RedisDelayHelper
{
    public function delay($msg)
    {
        $retryTs = time() + 5;
        Redis::zadd("delay-queue", $retryTs, $msg);
    }

    public function loop(callable $func)
    {
        while (true) {
            $value = Redis::zrangebyscore("delay-queue", 0, time(), 'limit', 0, 1);
            if (empty($value)) continue;
            if (Redis::zrem('delay-queue', $value[0])) {
                call_user_func_array($func, [$value[0]]);
            }
        }
    }

    public function loopLua(callable $func)
    {
        $script = <<<Lua
if redis.call("zrangebyscore","delay-queue", 0, KEYS[1], "limit", 0, 1) == ARGV[1] then
    return redis.call("del",KEYS[1])
else
    return 0
end
Lua;

    }
}
