<?php

namespace App\Http\Controllers;

use App\Http\Components\RedisLockHelper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Redis;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index(RedisLockHelper $redisLockHelper)
    {
        while (true) {
            if ($redisLockHelper->lock('laravel8')) {
                Redis::incr('aa');
                $redisLockHelper->unlock('laravel8');
                break;
            }
        }
    }
}
