<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthKepsek
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->session()->get('user')) {

            if ($request->session()->get('user')['role'] == 'kepala desa') {
                return $next($request);
            } else {
                return redirect('login')->with('failed', 'Akses ditolak ! Anda bukan Kepala Desa.');
            }
        }
        return redirect('login')->with('failed', 'Akses ditolak ! Anda bukan Kepala Desa.');
    }
}
