<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsLoggedIn
{

    private const AVOIDED_ROUTES = [
        'login',
        'login/callback',
        'logout',
        'doLogin',
        'doLogout',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // We need to retrieve the user from the session
        $user = $request->session()->get('user');

        // Retrieving the route
        $route = $request->route();
        $path = $route->uri();

        // User is not authenticated and is trying to navigate
        if (! $user) {
            // He is trying to access a page that requires authentication
            if (! in_array($path, self::AVOIDED_ROUTES)) {
                // Redirect to the login page
                return redirect()->route('login')->with('errors', 'You need to be logged in to access this page.');
            } else {
                // Continue to the request like this is a normal request
                return $next($request);
            }
        }

        // If the user is in the session and is visiting the login page or any authentication page, redirect to the home page
        if ($user && in_array($path, ['login', 'doLogin', 'login/authenticate'])) {
            return redirect()->route('home');
        }

        // Continue to the request like this is a normal request
        return $next($request);
    }
}
