<?php

namespace App\Http\Middleware;

use App\Support\AdminPermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps anyone without an admin role out of the admin entirely.
 *
 * The users table is shared with the public site, so having an account is not
 * enough; a role is what grants admin access. Which modules a role can open is
 * decided separately by route `permission:` middleware and Gate checks.
 */
class EnsureAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->hasAnyRole(AdminPermissions::roles())) {
            // Don't strand a signed-in user on a bare 403 with no way out.
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'That account does not have access to the WestHub admin. Ask a super admin to assign you a role.',
            ]);
        }

        return $next($request);
    }
}
