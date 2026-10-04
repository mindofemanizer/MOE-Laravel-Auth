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
            return $this->unauthorized($request, false);
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

        return $this->unauthorized($request, true);
    }

    /**
     * Tangani permintaan tak berwenang.
     *
     * Untuk permintaan API/JSON, kembalikan respons JSON 401/403 agar klien
     * (mobile/PWA) menerima kode status yang benar — bukan redirect HTML yang
     * tidak dapat diproses. Permintaan web tetap mendapat redirect seperti semula.
     */
    protected function unauthorized(Request $request, bool $authenticated): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $authenticated ? 'Akses ditolak.' : 'Tidak terautentikasi.',
            ], $authenticated ? 403 : 401);
        }

        $portal = $request->route()?->getAction()['as'] ?? '';
        $redirects = config('moe-auth.roles.redirects', []);

        foreach ($redirects as $key => $path) {
            if (str_contains((string) $portal, $key)) {
                return redirect($path);
            }
        }

        return redirect('/');
    }
}
