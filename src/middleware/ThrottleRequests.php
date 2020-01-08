<?php

namespace yunwuxin\throttle\middleware;

use Carbon\Carbon;
use Closure;
use think\App;
use think\Request;
use think\Response;
use yunwuxin\throttle\exception\ThrottleRequestsException;
use yunwuxin\throttle\RateLimiter;

class ThrottleRequests
{
    protected $limiter;
    protected $app;

    public function __construct(App $app, RateLimiter $limiter)
    {
        $this->limiter = $limiter;
        $this->app     = $app;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @param int $maxAttempts
     * @param int $decayMinutes
     * @param string $prefix
     * @return Response
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = '')
    {
        if ($prefix instanceof Closure) {
            $key = $this->app->invoke($prefix);
        } else {
            $key = $prefix . sha1($request->host() . '|' . $request->ip());
        }

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            throw $this->buildException($key, $maxAttempts);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        /** @var Response $response */
        $response = $next($request);

        $headers = $this->getHeaders($maxAttempts, $this->limiter->retriesLeft($key, $maxAttempts));

        return $response->header($headers);
    }

    protected function buildException($key, $maxAttempts)
    {
        $retryAfter = $this->limiter->availableIn($key);

        $headers = $this->getHeaders($maxAttempts, 0, $retryAfter);

        return new ThrottleRequestsException(
            'Too Many Attempts.', $headers
        );
    }

    protected function getHeaders($maxAttempts, $remainingAttempts, $retryAfter = null)
    {
        $headers = [
            'X-RateLimit-Limit'     => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ];

        if (!is_null($retryAfter)) {
            $headers['Retry-After']       = $retryAfter;
            $headers['X-RateLimit-Reset'] = Carbon::now()->addRealSeconds($retryAfter)->getTimestamp();
        }

        return $headers;
    }

}
