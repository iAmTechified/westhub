# WestHub: Setup, Integrations, Deployment and Handover

For whoever deploys and maintains WestHub, and the operations staff who configure it day to day.

WestHub is **one repository with two Laravel 11 applications** sharing **one MySQL database**:

| App | Folder in repo | Folder on the server | Serves |
| --- | --- | --- | --- |
| Public website | repo root | `/home/westgpac/westhub`, public files in `/home/westgpac/public_html` | The site visitors use |
| Admin | `westhub-admin/` | `/home/westgpac/westhub/admin`, public files in `admin/public` | The staff console on the admin subdomain |

Deployment is **cPanel Git Version Control**, driven by `.cpanel.yml`.

---

## 1. Where configuration lives

Configuration is split into two layers on purpose.

**Layer 1: `.env` files.** Anything that must work before anyone can sign in: database, app key, mail
transport, the shared encryption key, the shared uploads folder. Changing these means editing the
server's `.env` and clearing the config cache.

**Layer 2: Admin → Settings.** Everything the business changes: the promo campaign, which booking
provider is used, the Calendly link, Google accounts, spreadsheet IDs, sender addresses, the public
phone number. Stored in the shared `settings` table and **read live, so changes take effect
immediately with no deploy**.

When a value exists in both, **the admin setting wins**. The `.env` value is only a fallback for
before anyone has saved one.

---

## 2. Environment files

Copy `.env.example` to `.env` in **both** apps.

| Variable | Rule |
| --- | --- |
| `APP_KEY` | Different in each app. `php artisan key:generate` |
| `APP_ENV` / `APP_DEBUG` | `production` / **`false`** on the server, in both apps, always |
| `APP_URL` | The public site URL, and the admin URL |
| `DB_*` | **Identical** in both apps |
| `SETTINGS_ENCRYPTION_KEY` | **Identical** in both apps. See 2.1 |
| `PUBLIC_STORAGE_PATH` | **Identical** in both apps. See 2.2 |
| `PUBLIC_STORAGE_URL` | `/storage` |
| `WESTHUB_ADMIN_BASE_URL` | Public app only. The admin URL, for links in staff emails |
| `WESTHUB_PUBLIC_URL` | Admin app only. The public URL, for voucher links in emails the admin sends |
| `MAIL_*` | Both apps. See section 3 |
| `QUEUE_CONNECTION` | `database`. Needs the queue cron in 5.4 |

`APPOINTMENT_PROVIDER`, `CALENDLY_APPOINTMENT_URL`, `GOOGLE_CALENDAR_*` and `GOOGLE_SHEETS_*` are
still read, but only as fallbacks. **Configure them in the admin instead.**

### 2.1 The shared encryption key

The two apps have different `APP_KEY`s but share one settings table. Google service account keys are
stored encrypted, so without a shared key the admin could save a key the website can never read.

```
php artisan westhub:settings-key
```

Put the printed value in **both** `.env` files. The admin shows a warning on the Settings screen while
it is missing. If it is ever changed, re-enter the Google keys in the admin.

### 2.2 The shared uploads folder

Both apps must write uploads to, and serve them from, **one** folder. Otherwise an image uploaded in
the admin is saved in the admin's own storage and **404s on the public website**, which is the cause
of the broken article and gallery images.

In **both** `.env` files:

```
PUBLIC_STORAGE_PATH=/home/westgpac/westhub/storage/app/public
PUBLIC_STORAGE_URL=/storage
```

`.cpanel.yml` creates the `storage` links in both public folders on every deploy.

**One-time move of existing uploads.** Images already uploaded through the admin are sitting in the
admin's storage. Copy them across once, from cPanel Terminal:

```
cp -rn /home/westgpac/westhub/admin/storage/app/public/. /home/westgpac/westhub/storage/app/public/
```

`-n` never overwrites an existing file.

---

## 3. Email

The app sends through the cPanel mailbox on your hosting. Both apps need the same block.

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=mail.westhubhealthcare.com
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
MAIL_USERNAME=no-reply@westhubhealthcare.com
MAIL_PASSWORD="the mailbox password"
MAIL_FROM_ADDRESS="no-reply@westhubhealthcare.com"
MAIL_FROM_NAME="WestHub Healthcare"
```

Notes specific to this project:

- **Use `MAIL_ENCRYPTION`, not `MAIL_SCHEME`.** This project's `config/mail.php` reads
  `MAIL_ENCRYPTION`. Guides written for newer Laravel defaults say `MAIL_SCHEME`, which is ignored here.
- **Which host.** A mailbox created in cPanel uses `mail.<your domain>`. A **Namecheap Private Email**
  mailbox, bought separately, uses `mail.privateemail.com` instead. Check which product the mailbox
  lives in; the wrong host shows up as a connection timeout.
- **`MAIL_USERNAME` is the full address.** The part before the @ alone fails authentication.
- **The from address must be a real mailbox** on that domain, or the server rejects the message.
- **Publish SPF and DKIM** in the domain's DNS (cPanel → Email Deliverability repairs both). Without
  them, promo vouchers and application receipts land in spam.
- If port 465 is blocked, use `MAIL_PORT=587` with `MAIL_ENCRYPTION=tls`.

**Per-purpose sender addresses** (appointments, careers, enquiries, newsletter) are set in
**Admin → Settings → Mail**, not in `.env`.

**Test it:**

```
php artisan tinker --execute="Mail::raw('WestHub mail test', fn(\$m) => \$m->to('you@example.com')->subject('Test'));"
```

**Which emails need the queue cron.** Promo vouchers and promo staff alerts send immediately.
Job-application receipts, application staff alerts and application decision emails are **queued**, so
they only go out while the queue cron in 5.4 is running.

---

## 4. Integrations, configured in the admin

Each has a **Test connection** button that says exactly what is wrong.

### 4.1 Booking provider: Settings → Appointments

Choose **Calendly** or **Google Calendar**. The switch is live on the website immediately.

**Calendly.** Paste the scheduling URL. To move to another Calendly account, paste a different URL.

**Google Calendar** offers real open time slots inside the booking form, invites the client as an
attendee, and can attach a Google Meet link.

1. In Google Cloud Console, create a project and enable the **Google Calendar API**.
2. Create a **service account** and download its **JSON key**.
3. In Settings, paste the calendar ID, service account email, and the key. The key field accepts the
   whole JSON file or just the private key, and is stored encrypted.
4. **In Google Calendar, share the calendar with the service account address and grant "Make
   changes to events".** Without this, Google refuses every request.
5. Set booking days, hours, appointment length, minimum notice and booking window.
6. Save, then **Test connection**.

### 4.2 Google Sheets: Settings → Integrations

1. Enable the **Google Sheets API** on a service account (the Calendar one can be reused).
2. Copy the spreadsheet ID from its URL, the part between `/d/` and `/edit`.
3. Paste the ID, service account email and key in Settings.
4. **Share the spreadsheet with the service account address as an Editor.**
5. Save, then **Test connection**.

Two tabs are used, **Join Requests** and **Promo Claims**, both renameable and created automatically if
missing. Columns are matched **by name**, so staff can reorder them or add their own columns without
breaking the sync. `owner` and `notes` are never overwritten. Values are written as plain text, so an
applicant's name beginning with `=` cannot run as a formula.

**If Sheets was already configured through `.env`:** it keeps syncing exactly as before. The
**"Sync submissions to Google Sheets"** toggle only takes over once someone saves it in the admin.

### 4.3 The promo campaign: Settings → Promotions

Copy, start and end dates, popup timing, voucher rules and the staff alert list. **"Show the promo
popup on the website"** pauses the campaign instantly. Claims appear under **Promo Claims**.

---

## 5. Deploying

### 5.1 Before every push

The server has no Node.js, so **built assets are committed**. Build both apps and commit the output:

```
npm run build
cd westhub-admin && npm run build
```

Run both test suites:

```
php artisan test
cd westhub-admin && php artisan test
```

### 5.2 What cPanel does on deploy

`.cpanel.yml` syncs the code, publishes both public folders, links the shared uploads folder, runs
`composer install`, and caches config, routes and views, for both apps.

It **does not** run migrations, and it **does not** create the cron jobs. Both are manual, below.

### 5.3 Migrations, run by hand

From cPanel → Terminal:

```
cd /home/westgpac/westhub/admin
php artisan migrate:status
php artisan migrate --force
```

**Always read `migrate:status` first.** The admin app owns the schema. Some existing migrations
change data (for example normalising join-request statuses, or dropping an old articles column), so
review what is pending before running it.

The public app carries guarded copies of the same migrations so its own test database can be built.
You do not need to run migrations in the public app for this release; both of its new ones do nothing
once the admin migrations have run.

This release adds two migrations:

| Migration | What it does |
| --- | --- |
| `create_promo_claims_table` | New table for promo claims |
| `migrate_admin_access_permissions_to_roles` | Moves every admin from the old per-person `access_*` permissions onto a single role. See 6.2 |

Then seed roles and settings defaults. Both are safe to re-run and never overwrite operator changes:

```
php artisan db:seed --class=RolesAndPermissionsSeeder --force
php artisan db:seed --class=SettingsSeeder --force
```

> **Never run `php artisan db:seed` with no class on production.** It loads demo appointments,
> applications, locations and gallery items.

After changing any `.env` value on the server:

```
php artisan config:cache
```

### 5.4 Cron jobs, set once in cPanel → Cron Jobs

```
* * * * * cd /home/westgpac/westhub && php artisan schedule:run >> /dev/null 2>&1
* * * * * cd /home/westgpac/westhub/admin && php artisan schedule:run >> /dev/null 2>&1
* * * * * cd /home/westgpac/westhub && php artisan queue:work --stop-when-empty --max-time=55 >> /dev/null 2>&1
* * * * * cd /home/westgpac/westhub/admin && php artisan queue:work --stop-when-empty --max-time=55 >> /dev/null 2>&1
```

| Job | Without it |
| --- | --- |
| Public `schedule:run` | Expired promo vouchers keep showing as active |
| Admin `schedule:run` | SEO metrics stop updating |
| Both `queue:work` | Application emails never send and nothing reaches Google Sheets |

Shared hosting cannot keep a worker running permanently, so the queue runs as a one-minute cron that
exits when the queue is empty.

### 5.5 First deploy of this release, checklist

- [ ] `npm run build` in both apps, committed
- [ ] `SETTINGS_ENCRYPTION_KEY` identical in both server `.env` files
- [ ] `PUBLIC_STORAGE_PATH` identical in both, pointing at the shared folder
- [ ] Existing admin uploads copied into the shared folder (2.2)
- [ ] `APP_DEBUG=false` in both
- [ ] `migrate:status` reviewed, then `migrate --force` in the admin
- [ ] Roles and settings seeders run
- [ ] At least one `super_admin` exists (6.1)
- [ ] Cron jobs from 5.4 created
- [ ] `config:cache` run in both apps
- [ ] Mail tested with a real send
- [ ] An image uploaded in the admin loads on the public website
- [ ] Booking provider chosen and **Test connection** passing
- [ ] Promo dates and wording reviewed before the popup goes live

### 5.6 Remove the old setup script from the server

`cpanel_setup.php` has been deleted from the repository; `.cpanel.yml` now creates the storage links.

**If that file was ever uploaded to `public_html` and is still there, delete it now.** It contained a
hard-coded password, and **this GitHub repository is public**, so that password should be treated as
known to anyone.

---

## 6. Admin access and roles

### 6.1 Roles

The users table is shared with the public site, so having an account does not grant admin access. A
**role** does. Each person has exactly one.

| Role | Give it to |
| --- | --- |
| `super_admin` | Owners and the lead developer. Everything, including users and all settings |
| `ops` | Care coordinators. Appointments, enquiries, applications, promo claims, subscribers |
| `marketing` | Whoever runs the campaign. Promo claims and promo settings, newsletters, articles, gallery |
| `editor` | Content writers. Articles, gallery, care services |
| `reviewer` | Read-only across the admin, plus decisions on applications |

Every role can change its own password under Settings. Only a `super_admin` can manage users.
Marketing can edit **Promotions** settings but cannot see or change credentials, mail or the booking
provider.

Manage roles in **Admin → Users**, or from the command line:

```
php artisan westhub:admin-user --list x@y.z
php artisan westhub:admin-user person@westhubhealthcare.com --name="Their Name" --role=ops
php artisan westhub:admin-user person@westhubhealthcare.com --password="a new password"
```

**Locked out?** Almost always the account has no role, or no account exists. `--list` answers both.
An account with no role is signed straight back out with a message saying so, and the Users screen
marks it **No role**.

### 6.2 What the role migration did

Before this release, the Users screen granted individual `access_*` permissions to each person and no
role. The migration gives each such person the single role that covers the most of what they could
already open, choosing the least-privileged role on a tie. Anyone who already had a role is untouched.
The old permissions are then removed.

The result for each person is written to the Laravel log (`storage/logs/laravel.log`, search for
"Admin access migrated"). **Read it after migrating** and adjust anyone in Admin → Users whose access
is not quite right.

---

## 7. Handover

**The operations team needs no developer for:** pausing or editing the promo, switching between
Calendly and Google, changing either account, connecting a spreadsheet, changing sender addresses or
the public phone number, adding staff and assigning roles, and all content.

**A developer is needed for:** `.env` changes, deployments, migrations, cron jobs, and schema changes.

**Keep in a password manager, never in the repo:** cPanel login, domain and DNS login, mailbox
passwords, the Google service account JSON, `SETTINGS_ENCRYPTION_KEY`, both `APP_KEY`s, database
credentials, and the `super_admin` login.

### Troubleshooting

| Symptom | Check first |
| --- | --- |
| Promo popup never appears | Settings → Promotions: enabled, and end date in the future |
| Popup appears once, then never again | Expected. A dismissal is remembered for 7 days. Test in a private window |
| Vouchers not arriving | Mail settings, then spam folder, then SPF/DKIM |
| Application emails not arriving | The `queue:work` cron jobs |
| Nothing reaching the spreadsheet | Settings → Integrations → Test connection, then the queue cron |
| No times offered in the booking form | Settings → Appointments → Test connection. Usually the calendar was not shared with the service account |
| Admin-uploaded images 404 on the website | `PUBLIC_STORAGE_PATH` identical in both apps, and uploads copied into the shared folder |
| Every page errors after deploy | `npm run build` output not committed, or `config:cache` not re-run after a `.env` change |
| Someone cannot open a module | `westhub:admin-user --list`, then their role |

See also `docs/FEATURES.md` for how the promo campaign, booking providers and roles behave.
