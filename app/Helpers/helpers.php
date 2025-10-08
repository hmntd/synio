<?php

use Illuminate\Support\Facades\App;

if (!function_exists('tenant')) {
    /**
     * Get the current tenant or a specific tenant attribute.
     *
     * @param  string|null  $key  The attribute name (e.g. 'id', 'subdomain')
     * @return mixed|null
     */
    function tenant(?string $key = null)
    {
        // Check if a tenant instance exists in the container
        if (!App::has('tenant')) {
            return null;
        }

        $tenant = App::get('tenant');

        // Return the whole tenant model or a specific property
        return $key ? ($tenant->{$key} ?? null) : $tenant;
    }
}

if (!function_exists('is_tenant')) {
    /**
     * Determine if the current request is within a tenant context.
     */
    function is_tenant(): bool
    {
        return App::has('tenant');
    }
}

if (!function_exists('main_domain')) {
    /**
     * Get the main (non-tenant) domain of the app.
     *
     * @return string
     */
    function main_domain(): string
    {
        return config('app.domain');
    }
}
