<?php

namespace App\Support;

/**
 * The single source of truth for admin roles and permissions.
 *
 * The seeder, the middleware, the Users screen and the `westhub:admin-user`
 * command all read from here, so adding a role or a permission is a one-file
 * change.
 *
 * Access is granted through ROLES. Permissions are never given directly to a
 * user; this replaces the earlier `access_*` direct-permission scheme.
 */
class AdminPermissions
{
    public const SUPER_ADMIN = 'super_admin';
    public const EDITOR = 'editor';
    public const REVIEWER = 'reviewer';
    public const OPS = 'ops';
    public const MARKETING = 'marketing';

    /**
     * Permission name => human label, in `module.action` form.
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            'dashboard.view' => 'View the dashboard',

            'articles.view' => 'View articles',
            'articles.edit' => 'Create and edit articles',
            'articles.publish' => 'Publish and schedule articles',
            'articles.delete' => 'Trash, restore and permanently delete articles',

            'gallery.view' => 'View the gallery',
            'gallery.edit' => 'Upload, edit and delete gallery items',
            'gallery.publish' => 'Publish and archive gallery items',

            'join_requests.view' => 'View job applications',
            'join_requests.decide' => 'Contact and decide on job applications',

            'appointments.view' => 'View appointments',
            'appointments.manage' => 'Contact clients and manage appointments',

            'enquiries.view' => 'View enquiries',
            'enquiries.manage' => 'Assign and update enquiries',

            'promos.view' => 'View promo claims',
            'promos.manage' => 'Update promo claims and resend vouchers',
            'promos.export' => 'Export promo claims',

            'subscribers.view' => 'View newsletter subscribers',
            'subscribers.manage' => 'Subscribe, unsubscribe and delete subscribers',
            'subscribers.export' => 'Export the subscriber list',
            'subscribers.send' => 'Send a newsletter to every active subscriber',

            'care_services.view' => 'View care services',
            'care_services.manage' => 'Edit care services',

            'locations.view' => 'View locations and services',
            'locations.manage' => 'Edit locations and services',

            'users.view' => 'View admin users',
            'users.manage' => 'Create and edit admin users and assign roles',

            'settings.view' => 'View site settings',
            'settings.manage' => 'Change site settings, credentials and the booking provider',
        ];
    }

    /**
     * Role => permissions. `super_admin` is intentionally absent: it is granted
     * everything through the Gate::before bypass.
     *
     * `users.*` belongs to no role on purpose. Anyone who can assign roles can
     * make themselves a super admin, so that power stays with super_admin.
     *
     * @return array<string, array<int, string>>
     */
    public static function roleMatrix(): array
    {
        return [
            self::EDITOR => [
                'dashboard.view',
                'articles.view', 'articles.edit', 'articles.publish', 'articles.delete',
                'gallery.view', 'gallery.edit', 'gallery.publish',
                'care_services.view', 'care_services.manage',
                'locations.view',
            ],
            self::REVIEWER => [
                'dashboard.view',
                'articles.view',
                'gallery.view',
                'join_requests.view', 'join_requests.decide',
                'enquiries.view',
                'appointments.view',
                'promos.view',
                'locations.view',
            ],
            self::OPS => [
                'dashboard.view',
                'appointments.view', 'appointments.manage',
                'enquiries.view', 'enquiries.manage',
                'join_requests.view', 'join_requests.decide',
                'promos.view', 'promos.manage', 'promos.export',
                'subscribers.view', 'subscribers.manage',
                'locations.view', 'locations.manage',
                'gallery.view',
                'articles.view',
                'settings.view',
            ],
            self::MARKETING => [
                'dashboard.view',
                'promos.view', 'promos.manage', 'promos.export',
                'subscribers.view', 'subscribers.manage', 'subscribers.export', 'subscribers.send',
                'articles.view', 'articles.edit', 'articles.publish',
                'gallery.view', 'gallery.edit', 'gallery.publish',
                'settings.view',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function roles(): array
    {
        return array_merge([self::SUPER_ADMIN], array_keys(self::roleMatrix()));
    }

    /**
     * Human labels for the role picker.
     *
     * @return array<string, array{label: string, summary: string}>
     */
    public static function roleDescriptions(): array
    {
        return [
            self::SUPER_ADMIN => ['label' => 'Super admin', 'summary' => 'Everything, including users, roles and all settings.'],
            self::OPS => ['label' => 'Operations', 'summary' => 'Appointments, enquiries, job applications, promo claims and subscribers.'],
            self::MARKETING => ['label' => 'Marketing', 'summary' => 'The promo campaign, newsletters, articles and gallery.'],
            self::EDITOR => ['label' => 'Editor', 'summary' => 'Articles, gallery and care services.'],
            self::REVIEWER => ['label' => 'Reviewer', 'summary' => 'Read-only across the admin, plus decisions on job applications.'],
        ];
    }

    /**
     * The retired `access_*` direct permissions, each mapped to the new
     * permission that grants the same screen. Used once, by the migration that
     * moves existing users onto roles.
     *
     * `access_users` maps to nothing: only super admins could ever reach the
     * Users screen, and they already hold the super_admin role.
     *
     * @return array<string, string|null>
     */
    public static function legacyPermissionMap(): array
    {
        return [
            'access_articles' => 'articles.view',
            'access_applications' => 'join_requests.view',
            'access_appointments' => 'appointments.view',
            'access_gallery' => 'gallery.view',
            'access_care_services' => 'care_services.view',
            'access_locations' => 'locations.view',
            'access_subscribers' => 'subscribers.view',
            'access_settings' => 'settings.view',
            'access_users' => null,
        ];
    }

    /**
     * Pick the single role that best covers a set of retired permissions.
     *
     * Prefers the role that grants the most of them; on a tie, the role with
     * the fewest permissions overall, so nobody is given more than they need.
     *
     * @param  array<int, string>  $legacyPermissions
     * @return array{role: ?string, covered: array<int, string>, missing: array<int, string>}
     */
    public static function bestRoleForLegacy(array $legacyPermissions): array
    {
        $map = self::legacyPermissionMap();

        $wanted = array_values(array_unique(array_filter(array_map(
            static fn (string $legacy): ?string => $map[$legacy] ?? null,
            $legacyPermissions
        ))));

        if ($wanted === []) {
            return ['role' => null, 'covered' => [], 'missing' => []];
        }

        $best = null;

        foreach (self::roleMatrix() as $role => $grants) {
            $covered = array_values(array_intersect($wanted, $grants));
            $candidate = ['role' => $role, 'covered' => $covered, 'size' => count($grants)];

            if (
                $best === null
                || count($candidate['covered']) > count($best['covered'])
                || (count($candidate['covered']) === count($best['covered']) && $candidate['size'] < $best['size'])
            ) {
                $best = $candidate;
            }
        }

        return [
            'role' => $best['role'],
            'covered' => $best['covered'],
            'missing' => array_values(array_diff($wanted, $best['covered'])),
        ];
    }
}
