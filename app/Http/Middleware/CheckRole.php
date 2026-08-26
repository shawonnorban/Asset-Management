<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = auth()->user();

        // Not logged in
        if (!$user) {
            return redirect()->route('login');
        }

        // User has no role, or the role does not match
        if (!$user->inRoles($roles)) {
            abort(403, 'Access denied!');
        }

        return $next($request);
    }
}
