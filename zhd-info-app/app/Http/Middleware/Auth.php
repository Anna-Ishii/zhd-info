<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Auth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //リクエスト前の処理
        $user = $request->session()->get('member');
        if (!isset($user)) {
            return redirect()->route('auth');
        }
        $exists = User::where('id', $user->id)->exists();
        if (!$exists) {
            $request->session()->forget('member');
            return redirect()->route('auth');
        }
        return $next($request);
    }
}
