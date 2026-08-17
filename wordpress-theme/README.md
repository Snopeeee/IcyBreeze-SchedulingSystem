# IcyBreeze WordPress Public Website

This directory contains the portable WordPress theme for the public IcyBreeze marketing website.

WordPress owns the public pages and content presentation. Laravel remains the operational system for:

- appointment booking and management
- recurring care-plan subscriptions
- customer and payment records
- technician mobile accounts and GPS-assisted routing
- administration and dashboard reporting

## Requirements

- WordPress 6.6 or newer
- PHP 8.1 or newer
- the Laravel IcyBreeze application available at a public URL

## Install

1. Copy `icybreeze-public` into `wp-content/themes/`.
2. Add the Laravel application URL to `wp-config.php`:

   ```php
   define( 'ICYBREEZE_BOOKING_URL', 'https://booking.example.com' );
   ```

3. Activate **IcyBreeze Public** in WordPress.
4. Load the public website once. The theme creates and publishes Home, Services, How It Works, Coverage, FAQ, and Contact pages, then assigns Home as the static front page.
5. In **Settings → Permalinks**, select **Post name** and save if the server has not already refreshed its rewrite rules.

For local XAMPP development, the booking URL can be:

```php
define( 'ICYBREEZE_BOOKING_URL', 'http://127.0.0.1:8080' );
```

## Security

The package intentionally excludes `wp-config.php`, WordPress database content, local administrator credentials, uploads, and other machine-specific files. Configure secrets separately for every environment.

## Verification

See [design-qa.md](design-qa.md) for the desktop, mobile, interaction, asset, and browser-console validation performed against the Laravel visual target.
