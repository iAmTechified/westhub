# WestHub Admin

Standalone Laravel 11 + Livewire 4 admin application for WestHub healthcare operations.

## Setup

1. Install dependencies
   - `composer install`
   - `npm install`
2. Configure `.env` with the shared content database credentials.
3. Generate key: `php artisan key:generate`
4. Run migrations + seed roles/admin user: `php artisan migrate --seed`
5. Build assets: `npm run build`
6. Run app: `php artisan serve`

## Default Admin Seed

- Email: `admin@westhub.local` (or `ADMIN_EMAIL` in env)
- Password: `password` (or `ADMIN_PASSWORD` in env)

## Modules

- Dashboard
- Articles Studio (draft/autosave/publish/schedule/revisions)
- Join Requests (queue actions + decision email jobs)
- Appointments (view-only booking records, Calendly metadata visibility, admin email outreach)
- Gallery Management (multi-view + bulk status operations)
- Locations & Services matrix sync
- Settings and settings audit trail

## Background Jobs

- `App\\Jobs\\Mail\\SendJoinDecisionMessage`
- `App\\Jobs\\Seo\\IngestSeoMetricsJob`
- `App\\Jobs\\Appointments\\SyncAppointmentWithGoogleCalendar`

Schedule configured in `routes/console.php`.

## Appointment Calendar Integration

Set these env values to enable Google Calendar sync:

- `GOOGLE_CALENDAR_ID`
- `GOOGLE_SERVICE_ACCOUNT_EMAIL`
- `GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY`
- `GOOGLE_CALENDAR_TIMEZONE` (optional)
- `GOOGLE_CALENDAR_EVENT_DURATION_MINUTES` (optional)

## SMTP Setup For Appointment Emails

Configure these values in `.env`:

- `MAIL_MAILER=smtp`
- `MAIL_HOST=<your SMTP host>`
- `MAIL_PORT=<your SMTP port>`
- `MAIL_USERNAME=<your SMTP username>`
- `MAIL_PASSWORD=<your SMTP password>`
- `MAIL_ENCRYPTION=tls` (or `ssl` if your provider requires it)
- `MAIL_FROM_ADDRESS=<verified sender address>`
- `MAIL_FROM_NAME="WestHub Healthcare"`

After updating env values:

1. `php artisan config:clear`
2. `php artisan cache:clear`
