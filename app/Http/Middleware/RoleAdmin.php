<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user()['role'] == 'admin') {
            return $next($request);
        } else {
            Auth::logout();
            return redirect()->route('web.login')->withErrors(['login' => 'Bạn không phải là Admin. Hãy dùng tài khoản Admin để đăng nhập lại.']);
        }
    }
}
