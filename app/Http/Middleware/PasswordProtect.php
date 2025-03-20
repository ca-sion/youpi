<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PasswordProtect
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        session()->put('url.intended', $request->url());

        if ($this->isPasswordProtected() && $this->hasPasswords()) {
            if ($this->isProtectionOpen($request)) {
                return $next($request);
            }

            return redirect(route('protect'));
        }

        return $next($request);
    }

    /**
     * @param $request
     * @return bool
     */
    protected function isPasswordProtected()
    {
        return config('youpi.password_protected');
    }

    /**
     * @param $request
     * @return bool
     */
    protected function hasPasswords()
    {
        return ! empty(config('youpi.passwords'));
    }

    /**
     * @return bool
     */
    protected function isProtectionOpen($request)
    {
        return session()->has('protect_in');
    }
}
