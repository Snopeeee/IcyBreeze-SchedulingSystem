<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTechnician
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user('technician')) {
            return redirect()->route('technician.login');
        }

        abort_unless($request->user('technician')?->isTechnician(), 403);

        return $next($request);
    }
}
