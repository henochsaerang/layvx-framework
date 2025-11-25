<?php

namespace App\Middleware;

/**
 * Class AuthMiddleware
 *
 * Checks if a user is authenticated before allowing access to a route.
 */
class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * If the user is not logged in (checked via a session variable),
     * they are redirected to the login page.
     */
    public function handle()
    {
        // Check if the user session exists.
        // You can use any key you set upon successful login, e.g., 'user_id' or 'is_logged_in'
        if (!isset($_SESSION['user_id'])) {
            // User is not authenticated, redirect to the login page.
            // The route() function is available globally from helpers.php
            header('Location: ' . route('login'));
            exit(); // Stop script execution after redirect
        }
    }
}
