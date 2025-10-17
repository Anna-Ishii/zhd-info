<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAllowPage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $page_code): Response
    {
        $admin = session('admin');
        $allow_pages = $admin->allowpage()->pluck('code')->toArray();

        if(!in_array($page_code, $allow_pages, true)) abort(404, "権限がありません");
        return $next($request);
    }
}
