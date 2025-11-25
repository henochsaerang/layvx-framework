<?php

namespace App\Core;

use Closure;

/**
 * Defines the contract that all middleware must implement.
 */
interface Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \App\Core\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next);
}
