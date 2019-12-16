<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponser;
use Closure;
use Illuminate\Routing\Middleware\ThrottleRequests;

class CustomThrottleRequests extends ThrottleRequests
{
    use ApiResponser;

    /**
     * Create a 'too many attempts' exception.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     */
    protected function buildException($key, $maxAttempts)
    {
        $retryAfter = $this->getTimeUntilNextRetry($key);

        $headers = $this->getHeaders(
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts, $retryAfter),
            $retryAfter
        );

        return $this->errorResponse([
            'message' => 'Has excedido el numero de peticiones permitidas',
            'code' => 429,
            'headers' => $headers
        ],429);

        // return new ThrottleRequestsException(
        //     'Too Many Attempts.', null, $headers
        // );
    }
}
