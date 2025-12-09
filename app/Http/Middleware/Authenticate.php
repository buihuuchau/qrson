<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            abort(response()->json([
                'status' => false,
                'status_code' => 403,
                'message' => 'Hành động chưa xác thực, hãy đăng nhập lại!'
            ], 403));
        } else {
            if ($request->is('api/*')) {
                abort(response()->json([
                    'status' => false,
                    'status_code' => 403,
                    'message' => 'Api chưa được xác thực, hãy đăng nhập lại!'
                ], 403));
            } else {
                return route('web.login');
            }
        }
    }
}
