# Promo Campaign, Booking Providers and Admin Roles

How these three features behave, for the WestHub team. Setup and deployment are in `docs/DEPLOYMENT.md`.

---

## 1. The "1 Month Free Healthcare Services" promo

### What a visitor experiences

1. After a few seconds on any page, after scrolling part way, or when moving to leave on a desktop, a
   popup offers **1 Month FREE** of home care, nursing or therapeutic services.
2. They enter name, email, phone, the service they are interested in, and agree to be contacted.
3. A voucher code is generated, shown on screen and emailed to them.
4. **Book My First Appointment** opens the normal booking form with their details and voucher already
   filled in.
5. If they close the popup instead, a small reminder tab stays in the corner. Clicking it reopens the
   offer.

A dismissed popup stays away for 7 days, and never returns once someone has claimed. It never opens on
top of the booking form.

The **voucher link in the email works on any device**. Opening it goes straight to the booking form with
the voucher applied, even on a different phone or browser, and even after the campaign has been paused.

### What happens behind the scenes

Each claim stores one row in `promo_claims`, then, without ever failing the visitor's submission:

| Step | Detail |
| --- | --- |
| Voucher email | Sent to the client immediately |
| Staff alert | Sent immediately to the promo alert list, falling back to the enquiries address |
| Google Sheets | A row in the **Promo Claims** tab, if Sheets is connected |
| Newsletter | Consenting claimants join the subscriber list, if that option is on |

**One household, one voucher.** A repeat claim with the same email shows the original code instead of
issuing a second.

When an appointment is booked with a valid voucher, the claim is marked **redeemed** and linked to that
appointment. Expired, cancelled and already-redeemed vouchers are refused politely, and the person can
still book normally.

### How much admin it needs

Almost none. The campaign ships with its wording filled in. The admin is only for:

- **Pausing it**: Settings → Promotions → "Show the promo popup on the website".
- **Changing the end date or wording.**
- **Recording a follow-up call** or resending a voucher, under Promo Claims.
- **Exporting** the claim list, and watching the **conversion to booking** figure.

---

## 2. Booking providers: Calendly, Google booking page or Google Calendar

The provider is chosen in **Settings → Appointments**, and switching takes effect immediately.

**Calendly** works as before: the visitor's details are saved first, so a lead is never lost, then
Calendly opens pre-filled. Calendly's scripts are only loaded while Calendly is the active provider.

**Google booking page** uses Google Calendar's appointment schedules, with no Google Cloud setup. After the
visitor's details are saved, Google's own booking page is shown inside the booking window and the booking
goes straight onto the calendar. Google does not report the booking back, so the request stays **New** in
the admin and any promo voucher is redeemed by staff rather than automatically. See `DEPLOYMENT.md` 4.1.

**Google Calendar** keeps the whole booking inside the WestHub site:

- The visitor picks a date and a time. Only times that are genuinely free on the calendar are offered.
- The event is written to the calendar with the client **invited as an attendee**, so they get a real
  calendar invitation.
- A **Google Meet link** is added when that option is on.
- If two people pick the same slot at the same moment, the second is asked to choose again.

If the chosen provider is not configured, or there are no free times, the request is still saved and the
visitor is told a coordinator will call. The form never shows a broken screen.

---

## 3. Admin roles

Access is granted by **role**. Each person has exactly one, chosen in **Admin → Users** from a list that
shows what each role allows.

| Role | Can |
| --- | --- |
| Super admin | Everything, including users and every setting |
| Operations | Appointments, enquiries, applications, promo claims, subscribers |
| Marketing | Promo claims and promo settings, newsletters, articles, gallery |
| Editor | Articles, gallery, care services |
| Reviewer | Read everything, and decide on applications |

What this means in practice:

- **The sidebar only shows what a person can open**, and buttons they cannot use are hidden.
- **Every action is checked on the server**, not just the page. Hiding a button is a convenience; the
  check is what actually protects the data.
- **Everyone can change their own password**, whatever their role.
- **Only a super admin can manage users.** Nobody can delete themselves, demote themselves, or remove
  the last super admin.
- **Marketing can run the promo without touching credentials.** They can edit Promotions settings but
  cannot see Google keys, mail settings or the booking provider.

These rules live in one file, `westhub-admin/app/Support/AdminPermissions.php`. Changing what a role can
do is an edit there followed by `php artisan db:seed --class=RolesAndPermissionsSeeder --force`.
