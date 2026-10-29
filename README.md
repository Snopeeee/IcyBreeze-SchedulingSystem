# IcyBreeze Scheduling System

A Laravel 12 scheduling and operations system for IcyBreeze Aircon Cleaning in Iligan City. Laravel owns the public website, appointment and subscription records, administration, and reporting. The active areas are the public customer website and the secure admin dashboard. Local PHP and Docker can share XAMPP MySQL; the Railway deployment uses its own MySQL service. WordPress is not part of this application.

## Included features

- Public service, pricing, coverage, FAQ, and contact pages
- Four-step appointment booking with exact unit-type pricing and schedule conflict protection
- Private booking management links and a 24-hour online cancellation policy
- Quarterly and biannual maintenance-plan previews marked Coming Soon
- Cash-after-service payment records and admin reconciliation
- Admin dashboard, graphs, appointments, customers, services, payments, and subscriptions
- MySQL persistence with no sample customers, appointments, payments, or default passwords

## One-time XAMPP MySQL setup

Start MySQL in the XAMPP Control Panel. Create an empty database and a dedicated account that can connect from both Windows and Docker:

```sql
CREATE DATABASE IF NOT EXISTS icybreeze_scheduling
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'icybreeze_app'@'localhost' IDENTIFIED BY 'choose-a-strong-password';
CREATE USER IF NOT EXISTS 'icybreeze_app'@'%' IDENTIFIED BY 'choose-a-strong-password';
GRANT ALL PRIVILEGES ON icybreeze_scheduling.* TO 'icybreeze_app'@'localhost';
GRANT ALL PRIVILEGES ON icybreeze_scheduling.* TO 'icybreeze_app'@'%';
FLUSH PRIVILEGES;
```

Copy `.env.example` to `.env`, set the same `DB_PASSWORD`, and generate an application key if needed:

```powershell
cd C:\xampp\htdocs\SchedulingSystem
Copy-Item .env.example .env
C:\xampp\php\php.exe artisan key:generate
C:\xampp\php\php.exe artisan migrate --seed
```

The production seeder is safe to run repeatedly: it creates only the real service and price catalog.

## Create the first administrator

```powershell
C:\xampp\php\php.exe artisan app:create-admin admin@your-domain.com
```

The command securely prompts for a strong password. Customers use the public booking form; their private links show their own booking details.

If an existing local administrator forgets the password, reset only that password with an interactive, hidden prompt:

```powershell
C:\xampp\php\php.exe artisan app:reset-local-admin-password admin@icybreeze.local
```

This command is local-only and does not change the administrator's email, phone, name, or role. Do not put a password on a command line or in a chat message.

## Run locally with XAMPP MySQL

With XAMPP Apache and MySQL running, open `http://localhost/SchedulingSystem/public/`. The admin area is at `/SchedulingSystem/public/admin`. Laravel's `public` folder is the web entry point; do not expose the application root as a website.

Alternatively, use Laravel's local development server:

```powershell
npm install
npm run build
C:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8000
```

- Website: `http://127.0.0.1:8000`
- Admin: `http://127.0.0.1:8000/admin`

## Run the Laravel application in Docker

Docker packages Apache, PHP 8.3, Composer dependencies, and the production Vite assets. It connects to the same XAMPP MySQL database through `host.docker.internal`, so local PHP and Docker see the same records.

Keep XAMPP MySQL running, then execute:

```powershell
docker compose up --build -d
```

Open `http://127.0.0.1:8080`. The container waits for MySQL, runs outstanding migrations, ensures the price catalog exists, and starts Apache.

Useful commands:

```powershell
docker compose ps
docker compose logs -f app
docker compose down
```

Do not use `docker compose down -v` unless you intentionally want to delete Docker-managed application storage and the generated container application key. MySQL data remains managed by XAMPP.

## Short-lived Render presentation deployment

`render.yaml` describes a separate free Docker web service and free PostgreSQL database for a school demonstration. It does **not** migrate or overwrite the XAMPP MySQL database. Render's free PostgreSQL database expires after 30 days and has no backups; do not use it for real customer records. The free web service can sleep when idle, so allow time for its first request before presenting.

Create a Render Blueprint from this repository and select the branch containing `render.yaml`. The Blueprint generates a persistent app encryption key and links the private PostgreSQL URL. After the initial web-service deployment, add `ADMIN_EMAIL` and a new strong `ADMIN_PASSWORD` privately in the Render service's Environment page; do not share the password in chat or Git. Add the PayPal **Sandbox** `PAYPAL_CLIENT_ID` and `PAYPAL_CLIENT_SECRET`, `OPENWEATHER_API_KEY`, `GOOGLE_CALENDAR_CLIENT_ID`, and `GOOGLE_CALENDAR_CLIENT_SECRET` there too. Redeploy so the first administrator is created and the integrations can be verified. The hosted app is marked as a demo and uses `APP_ENV=staging`, `APP_DEBUG=false`, and `PAYPAL_MODE=sandbox` so test payments cannot be confused with live payments.

After Render assigns a `*.onrender.com` hostname, add its exact `https://.../admin/integrations/google-calendar/callback` URI to the Google Cloud OAuth Web client. Sign in to the demo administrator account and reconnect Google Calendar; the local refresh token does not transfer. Verify all five integrations on `/admin/integrations` against the hosted environment. Before any real launch, move to durable database hosting, set `APP_ENV=production`, disable Sandbox checkout, review security and backups, and replace the educational keyless API usage as needed.

## Retired technician module

Technician logins, account management, job assignment, GPS collection, and route optimization are no longer available. Existing migrations and historical database fields remain for data preservation; no customer, booking, payment, or subscription records are deleted. Older assigned appointments are treated as confirmed and continue to block overlapping bookings. The existing price catalog and financial splits remain unchanged.

## Brand typography

League Spartan Bold (700) is used for headings and display figures. Poppins Regular (400) and SemiBold (600) are used for body text and controls. Fonts are bundled locally through Vite; no external font service is required. The current color palette is unchanged.

## Business profile

The public site reads the official contact, location, social, and operating-hour details from `config/business.php`. The defaults match the IcyBreeze Facebook profile: Tambacan, Iligan City; 0967 873 0654; icybreezeaccleaning@gmail.com; Facebook `icybreezeac`; and Instagram `@icybreezeph`. Each value can be changed later through the corresponding `BUSINESS_*` environment variable without editing page templates.

## Final-project features and external APIs

Customer accounts can register, sign in, book while signed in, and see only the appointments made under that account. Earlier guest appointments retain their private management links and are not automatically claimed by an unverified email address. Administrators have a complete FAQ create/read/update/delete workflow; published answers appear on the public FAQ page. Booking, payments, service prices, and the admin dashboard continue to use the application database.

The admin **API Integrations** page presents exactly five integrations: PayPal Sandbox, OpenWeather, Google Calendar, Open-Meteo Air Quality, and OpenStreetMap Nominatim. The page does not expose credentials, send private customer addresses to public mapping services, or let an API outage alter stored bookings.

| API | Purpose | Access |
| --- | --- | --- |
| PayPal Sandbox | Test-only payment on a private booking page | Encrypted local Sandbox credentials or `PAYPAL_CLIENT_ID` and `PAYPAL_CLIENT_SECRET` |
| OpenWeather | Rain and temperature forecast for Iligan service windows | Encrypted local key or `OPENWEATHER_API_KEY` |
| Google Calendar | Administrator-authorized appointment sync | OAuth client and admin consent |
| Open-Meteo Air Quality | Current outdoor AQI and PM2.5 advisory | Keyless non-commercial school demo; attribution shown |
| OpenStreetMap Nominatim | Admin-initiated, public Iligan City service-area check | Keyless; cached and rate-limited, with attribution |

Nominatim is queried only on an admin action, at most once per minute per user, and only with the city name—never a customer's street address. The result must fall within the Iligan area before it is shown. The public Open-Meteo endpoint is limited to non-commercial educational use and should be replaced with a commercial license or another source before commercial operation.

The retired Resend, Twilio, Google Maps Geocoding, Nager.Date, and Sunrise-Sunset integrations are not part of the application. No provider-specific credentials are committed to Git. PayPal Sandbox checkout is disabled in production and cannot take real customer payments.

Google Calendar uses a separate OAuth Web client. In Google Cloud, enable the Google Calendar API, leave the app in **Testing**, add the administrator's Google account as a test user, and authorize the exact callback URI shown on the admin integrations page. For local development, the OAuth Client ID and Secret may be stored through the signed localhost setup screen in encrypted MySQL; alternatively set `GOOGLE_CALENDAR_CLIENT_ID` and `GOOGLE_CALENDAR_CLIENT_SECRET` in the environment. The default target is the authorized account's primary calendar; `GOOGLE_CALENDAR_ID` can select a different calendar. The administrator must then use **Connect Google Calendar** and approve the requested `calendar.events.owned` scope. On each confirmed appointment page, **Sync to Google Calendar** creates or updates a private event containing only the booking reference, time, and status. Cancelled/no-show appointments remove their corresponding event. Sync is manual; existing bookings and payments do not depend on Google Calendar availability. A local OAuth client and redirect URI cannot be reused on a future host without updating Google Cloud settings.

Every booking still creates a cash-after-service payment record. When PayPal is configured in a **non-production** environment, the customer can test online payment from the private booking page using `PAYPAL_MODE=sandbox` and a PayPal Sandbox buyer account. The server creates and captures a test order, then verifies its appointment reference, currency, amount, and completed capture before marking the test payment paid. This is test money only; Sandbox checkout is disabled in production. If PayPal is unconfigured or unreachable, cash remains available. The administrator can verify the Sandbox credentials without creating an order from the API Integrations page. If the customer return flow is interrupted after capture, an admin can verify the pending order with PayPal on the Payments page. An admin can switch an uncaptured PayPal order back to cash after the provider confirms it has no capture. PayPal refunds and automated webhook reconciliation are not included yet.

For a local presentation, open `/admin/integrations` after administrator sign-in. The page shows all five selected services. Use **Verify Sandbox connection** to test PayPal credentials without making a payment; review the three OpenWeather forecast windows; use **Verify access** for Google Calendar without changing events; read the Open-Meteo AQI; and use **Verify Iligan City** for the OpenStreetMap lookup. To demonstrate Sandbox checkout, create a test booking and use its private management page with a Sandbox buyer account. Do not use a real customer booking or real PayPal account for this test.

## Test

Automated tests use an isolated in-memory SQLite database and never touch XAMPP MySQL:

```powershell
C:\xampp\php\php.exe artisan test
```
