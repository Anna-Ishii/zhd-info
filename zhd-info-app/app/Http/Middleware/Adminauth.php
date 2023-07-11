<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Adminauth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //リクエスト前の処理
        $admin = $request->session()->get('admin');
        if (!isset($admin)) {
            return abort(404);
        }
        $exists = Admin::where('id', $admin->id)->exists();
        if (!$exists) {
            $request->session()->forget('admin');
            return abort(404);
        }
        return $next($request);
    }
}
