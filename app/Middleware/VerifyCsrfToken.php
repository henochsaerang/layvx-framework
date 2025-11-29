<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Session;
use Closure;

class VerifyCsrfToken implements Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \App\Core\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Start session if not already started
        Session::start();

        // Don't check for CSRF on "reading" methods
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return $next($request);
        }

        $token = $request->input('tuama_token');
        $sessionToken = Session::token();

        if (is_null($token) || is_null($sessionToken) || !hash_equals($sessionToken, $token)) {
            // Stop processing and show an error
            http_response_code(419);
            die('419 - Page Expired. Invalid CSRF token.');
        }

        return $next($request);
    }
}
