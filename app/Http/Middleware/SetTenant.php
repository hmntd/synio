<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $domain = explode('.', $request->getHost(), 2)[0];

        $tenant = Tenant::where('subdomain', $domain)->first();

        if ($tenant) {
            app()->instance('tenant', $tenant);
        }

        return $next($request);
    }
}
