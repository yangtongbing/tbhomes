<?php

namespace App\Http\Middleware;

use Closure;


class AuthVerify
{
    /**
     * Handle an incoming request.
     * 处理传入的请求
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (empty($request->session()->get('webSign')) == true) {
            return redirect('web/login');
        }

        return $next($request);
    }
}
