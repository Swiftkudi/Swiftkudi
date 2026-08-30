# SwiftKudi Marketplace Redesign — Production Release Guide

## Release scope

This release keeps the SwiftKudi brand and indigo identity while restructuring the application into a professional freelancing marketplace centered on Find Work, Find Talent, Services, Proposals, Contracts, Milestones, Messages, Wallet/Escrow, Reviews and Notifications.

Major implementation areas include the marketplace navigation/design system, freelancer profiles, job discovery and proposals, contract/milestone workrooms, private messaging attachments, centralized notification preferences, Web Push, queued transactional email, email delivery diagnostics, SEO sitemaps/slugs/schema, rate limiting, security headers and release audits.

## Before deployment

1. **Back up the database and uploaded files.** Test migrations against a staging copy first.
2. **Rotate every credential that existed in any older source archive** (database, SMTP, payment, OAuth, API, VAPID or other secrets). This release intentionally contains examples only, not a production `.env`.
3. Use a currently supported PHP 8.x runtime compatible with the installed Laravel 8 dependencies. Do not attempt a Laravel major-version upgrade in the same production change.
4. Create `.env` from `.env.production.example`, set `APP_URL` to the exact HTTPS origin, generate/set `APP_KEY`, database credentials and provider credentials.
5. Use HTTPS in production. `SESSION_SECURE_COOKIE=true` is already documented in the production example.

## Deployment commands

Run from the project root after uploading the release:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

The final marketplace design also ships as `public/css/marketplace.css`, so the redesigned UI is not dependent on a fresh Tailwind build. If Node is available, rebuilding the original app assets is still recommended:

```bash
npm ci
npm run production
```

After any build, confirm `public/css/marketplace.css` remains deployed; it is the compatibility/design layer for both new and legacy marketplace views.

## Queues and scheduler

Production notifications/email should not run with `QUEUE_CONNECTION=sync`. The production example uses Redis.

A typical Supervisor worker command is:

```bash
php artisan queue:work redis --queue=notifications,default --sleep=2 --tries=4 --timeout=90
```

Run Laravel's scheduler once per minute from cron:

```cron
* * * * * cd /path/to/swiftkudi && php artisan schedule:run >> /dev/null 2>&1
```

The scheduler includes daily and weekly non-critical notification digests. Messages, proposals, contracts, payments, disputes and security notices remain immediate.

## Email deliverability / spam reduction

Application code can improve deliverability but cannot guarantee inbox placement. Recipient mail providers make the final spam decision.

For the exact domain used in `MAIL_FROM_ADDRESS` / Admin SMTP settings:

- Publish **one valid SPF policy** that authorizes the actual sending provider.
- Enable provider **DKIM signing** and publish the supplied DKIM DNS records.
- Publish **DMARC**. Start with monitoring (`p=none`) while validating legitimate sources, then move toward `quarantine`/`reject` once alignment is proven.
- Ensure the visible From domain aligns with SPF and/or DKIM for DMARC.
- If self-hosting SMTP, configure forward/reverse DNS (PTR), TLS and a stable sending IP. A reputable transactional provider is usually safer.
- Keep transactional mail separate from bulk marketing campaigns where possible.
- Do not send to repeatedly bouncing/invalid recipients. Provider webhook integration is required for authoritative bounce/complaint suppression.
- Warm a new sending domain/IP gradually instead of sending a sudden large volume.

Admin → Email Delivery Diagnostics records queue/transport attempts and correlation IDs. A `sent` state means the configured mail transport accepted the message; it does **not** prove inbox placement. Provider delivery/bounce/complaint webhooks are required for that next level of telemetry.

## Browser push

Configure:

```env
VAPID_SUBJECT=mailto:notifications@your-domain.example
VAPID_PUBLIC_KEY=...
VAPID_PRIVATE_KEY=...
```

Push requires HTTPS (except local development). Users explicitly enable browser push from Notification Settings. Stale subscriptions are automatically removed when providers report them invalid.

## SEO verification

After deployment verify anonymously:

- `/robots.txt`
- `/sitemap.xml`
- `/sitemap-pages.xml`
- `/sitemap-jobs.xml`
- `/sitemap-services.xml`
- `/sitemap-freelancers.xml`
- canonical URLs on job/service/freelancer pages
- JobPosting structured data on eligible active jobs

Authenticated/private layouts default to `noindex,nofollow`; admin and authentication layouts are explicitly `noindex,nofollow`.

## Security checklist

- `APP_DEBUG=false`
- HTTPS only
- rotate historical secrets
- production secure cookies enabled
- Admin security/rate-limit settings reviewed
- queue worker runs under an unprivileged service account
- writable permissions limited to Laravel storage/cache paths
- web server denies `.env`, repository metadata and private storage paths
- payment/webhook provider signature validation retained and tested
- database user follows least privilege

The global security middleware adds CSP, anti-framing, MIME sniffing protection, referrer policy, permissions policy and HSTS on secure production requests. The CSP intentionally still permits legacy inline scripts/styles for compatibility; migrate those to nonce/hash-based CSP in a separate controlled hardening release rather than breaking existing marketplace JavaScript.

## Release verification

Before upload, run:

```bash
bash scripts/release_audit.sh
```

Then, once Composer dependencies and a test database are available on staging, additionally run:

```bash
php artisan route:list
php artisan test
```

Perform browser QA for anonymous visitor, freelancer, client and admin roles, including mobile widths. Test this critical path end-to-end:

`Register → complete profile → find/post job → proposal → hire → contract → fund milestone → submit → approve/release → review`

Also test:

`Marketplace event → in-app notification + push (when enabled) + email (according to preference/frequency)`
