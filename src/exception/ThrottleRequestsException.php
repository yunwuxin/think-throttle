<?php

namespace yunwuxin\throttle\exception;

use think\exception\HttpException;

class ThrottleRequestsException extends HttpException
{
    public function __construct(string $message = '', array $headers = [])
    {
        parent::__construct(429, $message, null, $headers);
    }
}
