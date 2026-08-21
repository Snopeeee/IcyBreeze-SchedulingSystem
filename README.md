# IcyBreeze Scheduling System

A Laravel 12 scheduling and operations system for IcyBreeze Aircon Cleaning in Iligan City. Laravel owns the public website, appointment and subscription records, administration, technician mobile accounts, GPS locations, routing data, and reporting. MySQL in XAMPP is the single database for both local PHP and Docker.

## Included features

- Public service, pricing, coverage, FAQ, and contact pages
- Four-step appointment booking with exact unit-type pricing and schedule conflict protection
- Private booking management links and a 24-hour online cancellation policy
- Quarterly and biannual care-plan requests
- Cash-after-service payment records and admin reconciliation
- Admin dashboard, graphs, appointments, customers, services, payments, subscriptions, and technician management
- Technician mobile accounts, assigned jobs, consent-based GPS updates, and route ordering
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

The command securely prompts for a strong password. Technicians are then created from **Admin → Technicians**.

## Run locally with XAMPP MySQL

```powershell
npm install
npm run build
C:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8000
```

- Website: `http://127.0.0.1:8000`
- Admin: `http://127.0.0.1:8000/admin`
- Technician: `http://127.0.0.1:8000/technician`

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

## Payments

The active payment workflow is cash after service. Every booking creates an unpaid payment record; an administrator marks it paid after collection. No simulated card, e-wallet, or gateway transaction is created. A real online gateway should only be enabled after provider credentials, signed webhooks, idempotency, and reconciliation are implemented.

## Test

Automated tests use an isolated in-memory SQLite database and never touch XAMPP MySQL:

```powershell
C:\xampp\php\php.exe artisan test
```
