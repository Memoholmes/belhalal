<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Session;
use URL;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::check()) {
            $userAuthInfo = getUserAuthInfo(10);
            if ($request->ajax()) {
                return __apiResponse([
                    'auth_info' => $userAuthInfo,
                ], 10);
            }

            Session::put('intendedUrl', URL::current());

            // Check if current user is admin
            return redirect()->route('user.profile_view', ['username' => getUserAuthInfo('profile.username')]);
        }

        return $next($request);
    }
}
