<?php

namespace yunwuxin\throttle\facade;

use think\Facade;

/**
 * Class RateLimiter
 * @package yunwuxin\throttle\facade
 * @mixin \yunwuxin\throttle\RateLimiter
 */
class RateLimiter extends Facade
{
    protected static function getFacadeClass()
    {
        return \yunwuxin\throttle\RateLimiter::class;
    }
}
