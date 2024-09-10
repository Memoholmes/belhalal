<?php

namespace App\Http\Middleware;

use Closure;

class CommonEntranceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next)
    {
        require app_path('Yantrana/Support/custom-tech-config.php');

        return $next($request);
    }
}
