<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Models\Admin;
use App\Core\Session;
use Closure;

/**
 * Class AuthAdminMiddleware
 *
 * Checks if an admin is authenticated before allowing access to a route.
 */
class AuthAdminMiddleware implements Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $userId = Session::get('user_id');

        // If there's no user ID in the session, redirect to login
        if (!$userId) {
            return Response::redirect('/login-admin');
        }

        // 2. Use the Admin model to find the user in the database
        $admin = Admin::find($userId);

        // If no user is found for the ID in the session, the session is invalid.
        if (!$admin) {
            Session::forget('user_id'); // Clear the invalid session
            return Response::redirect('/login-admin');
        }

        // Attach the user object to the request for easy access in controllers
        $request->setUser($admin);

        // 3. User is authenticated and valid. Proceed with the request.
        return $next($request);
    }
}
