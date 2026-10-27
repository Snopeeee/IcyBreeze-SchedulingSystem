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

## Retired technician module

Technician logins, account management, job assignment, GPS collection, and route optimization are no longer available. Existing migrations and historical database fields remain for data preservation; no customer, booking, payment, or subscription records are deleted. Older assigned appointments are treated as confirmed and continue to block overlapping bookings. The existing price catalog and financial splits remain unchanged.

## Brand typography

League Spartan Bold (700) is used for headings and display figures. Poppins Regular (400) and SemiBold (600) are used for body text and controls. Fonts are bundled locally through Vite; no external font service is required. The current color palette is unchanged.

## Business profile

The public site reads the official contact, location, social, and operating-hour details from `config/business.php`. The defaults match the IcyBreeze Facebook profile: Tambacan, Iligan City; 0967 873 0654; icybreezeaccleaning@gmail.com; Facebook `icybreezeac`; and Instagram `@icybreezeph`. Each value can be changed later through the corresponding `BUSINESS_*` environment variable without editing page templates.

## Five optional API integrations

The application supports PayPal, Resend, Twilio, Google Maps Geocoding, and OpenWeather. Configure each in `.env` for local PHP or Docker, or in Railway service variables. The admin **API Integrations** page shows configuration state without exposing keys. Unconfigured services do not make external requests.

| Provider | Purpose | Required variables |
| --- | --- | --- |
| PayPal | Optional payment on the private booking page | `PAYPAL_ENABLED`, `PAYPAL_MODE`, `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET` |
| Resend | Booking confirmation email | `RESEND_API_KEY`, `RESEND_FROM_ADDRESS` |
| Twilio | Booking confirmation SMS | `TWILIO_ACCOUNT_SID`, `TWILIO_AUTH_TOKEN`, `TWILIO_FROM_NUMBER` |
| Google Maps | Admin-initiated address lookup | `GOOGLE_MAPS_GEOCODING_KEY` |
| OpenWeather | Cached Iligan forecast in admin | `OPENWEATHER_API_KEY` |

Every booking still creates a cash-after-service payment record. When PayPal is configured, the customer can choose online payment from the private booking page. The server creates and captures a PayPal order, then verifies its appointment reference, currency, amount, and completed capture before marking the payment paid. In production, PayPal is shown only with `PAYPAL_MODE=live` and live merchant credentials. Sandbox credentials are for local testing. If PayPal is unconfigured, cash remains the available method. If the customer return flow is interrupted after capture, an admin can verify the pending order with PayPal on the Payments page. An admin can switch an uncaptured PayPal order back to cash after the provider confirms it has no capture. PayPal refunds and automated webhook reconciliation are not included yet.

Resend requires a verified sending domain. Twilio requires an approved sender and may require a verified test recipient on trial accounts. Google Maps Geocoding requires a Google Cloud project with billing enabled. OpenWeather requires its own API key. These providers may charge for usage. The address lookup runs only when an admin requests it and does not store coordinates or track the customer.

## Test

Automated tests use an isolated in-memory SQLite database and never touch XAMPP MySQL:

```powershell
C:\xampp\php\php.exe artisan test
```
