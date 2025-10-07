<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect(config('app.url'));
        }

        $user = auth()->user();
        $domain = explode('.', $request->getHost(), 2)[0];
        $tenant = Tenant::where('subdomain', $domain)->first();
        $userTenant = $user->tenant;
        if (!$tenant || $tenant->id !== $user->tenant_id) {
            return Inertia::location($request->getScheme() . '://' . $userTenant->subdomain . '.' . config('app.domain') . $request->getPathInfo());
        }

        return $next($request);
    }
}
