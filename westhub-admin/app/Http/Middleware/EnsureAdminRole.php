<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'You are not authorized to access this admin area.');
        }

        // Allow if user has an admin role OR any admin-level permission
        $hasAdminRole = $user->hasAnyRole(['super_admin', 'editor', 'reviewer', 'ops']);
        $hasAdminPermission = $user->hasAnyPermission([
            'access_articles', 
            'access_applications', 
            'access_appointments', 
            'access_gallery', 
            'access_care_services', 
            'access_locations', 
            'access_settings'
        ]);

        if (! $hasAdminRole && ! $hasAdminPermission) {
            abort(403, 'You are not authorized to access this admin area.');
        }

        return $next($request);
    }
}
