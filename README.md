# Swiftkudi

A project integrated with Hovertask platform.

## Description

Swiftkudi is integrated with Hovertask - a platform for earning, advertising, and marketplace activities.

## Features

- User authentication
- Task management
- Advertising platform
- Marketplace
- Wallet system

## Getting Started

Visit the Hovertask platform to get started with Swiftkudi.

## Marketplace Subdomain (Local Development)

To run the student marketplace on a subdomain locally (recommended for session sharing and subdomain routing):

- Add an entry to your hosts file mapping the marketplace domain to localhost, for example:

	- Windows (run as Administrator): add `127.0.0.1 campus.localhost` to `C:\Windows\System32\drivers\etc\hosts`.

- Ensure your `APP_URL` (in `.env`) points to the main app (e.g. `http://localhost`) and set `APP_DOMAIN` to `localhost` or your base domain.
- Set `MARKETPLACE_SUBDOMAIN` in `.env` to `campus` (the app builds the full marketplace domain from this).
- Session cookies are configured in `config/session.php` to use `APP_DOMAIN` so that authentication is shared across the main site and the subdomain.

Example `.env` entries:

```
APP_URL=http://localhost
APP_DOMAIN=localhost
MARKETPLACE_SUBDOMAIN=campus
```

After updating hosts and `.env`, restart your local server. Marketplace routes will be available at `http://campus.localhost` and are namespaced under `marketplace.*` (onboarding routes are `marketplace.onboarding.*`).

## License

MIT
