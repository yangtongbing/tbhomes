<?php

namespace App\Http\Middleware;

use Closure;

class AtlasVerify
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (empty($request->session()->get('atlas')) == true) {
            return redirect('atlas/login');
        }
        return $next($request);
    }
}
