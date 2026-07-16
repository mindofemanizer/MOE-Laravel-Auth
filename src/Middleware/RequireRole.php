<?php

namespace Moe\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    public function handle(Request $request, Closure $next, string ...$portals): Response
    {
        if (! $request->user()) {
            return $this->unauthorized($request);
        }

        // If no portals specified, just check authentication
        if (empty($portals)) {
            return $next($request);
        }

        $userRole = $request->user()->role ?? null;
        $config = config('moe-auth.roles.portals', []);

        foreach ($portals as $portal) {
            $allowedRoles = $config[$portal] ?? [$portal];

            if (in_array($userRole, $allowedRoles)) {
                return $next($request);
            }
        }

        return $this->unauthorized($request);
    }

    protected function unauthorized(Request $request): Response
    {
        $portal = $request->route()->getAction()['as'] ?? '';
        $redirects = config('moe-auth.roles.redirects', []);

        foreach ($redirects as $key => $path) {
            if (str_contains($portal, $key)) {
                return redirect($path);
            }
        }

        return redirect('/');
    }
}
