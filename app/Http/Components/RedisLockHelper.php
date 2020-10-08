<?php
/**
 * RedisLockHelper.php
 * @author   wangyingjie <930055912@qq.com>
 * @date     2020/9/22
 * PhpStorm
 * @desc:
 */

namespace App\Http\Components;



use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class RedisLockHelper
{
    const IF_NOT_EXISTS = 'NX';
    const MILLISECOND_EXPIRE_TIME = 'PX';
    const EXPIRE_TIME = 60000; // millisecond => 60s

    public $token;

    /**
     * 加锁
     * @param $key
     * @param string $expire_time 60000
     * @return bool
     */
    public function lock($key, $expire_time='')
    {
        $this->token = Str::random();
        if (empty($expire_time)) {
            $expire_time = self::EXPIRE_TIME;
        }

        return Redis::set($key, $this->token, self::IF_NOT_EXISTS, self::MILLISECOND_EXPIRE_TIME, $expire_time);
    }

    /**
     * 解锁
     * 防止: 这个锁处理的事务A超时了, 被另一个事务B拿到锁继续执行, 当事务A执行结束时调用del, 把事务B的锁给取消了
     *
     * 参考： https://github.com/phpredis/phpredis/blob/develop/tests/RedisTest.php
     * @param $key
     * @return mixed
     */
    public function unlock($key)
    {
        $lua =<<<EOT
if redis.call("get",KEYS[1]) == ARGV[1] then
    return redis.call("del",KEYS[1])
else
    return 0
end
EOT;
        return Redis::eval($lua, 1, $key, $this->token);
    }
}
