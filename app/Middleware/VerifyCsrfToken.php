<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Session;
use Closure;
use Exception;

class VerifyCsrfToken implements Middleware {
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        // 'api/*'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \App\Core\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next) {
        if ($this->isReading($request) || $this->inExceptArray($request)) {
            return $next($request);
        }

        $token = $request->input('tuama_token');
        $sessionToken = Session::token();

        if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
            // It's better to throw an exception that can be caught by an error handler
            // to show a specific "419 | Page Expired" error page.
            throw new Exception('CSRF token mismatch.', 419);
        }

        return $next($request);
    }

    /**
     * Determine if the request is a reading request.
     *
     * @param  \App\Core\Request  $request
     * @return bool
     */
    protected function isReading($request) {
        return in_array($request->method(), ['HEAD', 'GET', 'OPTIONS']);
    }

    /**
     * Determine if the request has a URI that should be excluded.
     *
     * @param  \App\Core\Request  $request
     * @return bool
     */
    protected function inExceptArray($request) {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->uri() === $except) {
                return true;
            }
        }

        return false;
    }
}
