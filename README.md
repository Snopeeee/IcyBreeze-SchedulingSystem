# IcyBreeze Scheduling System

A Laravel 12 MVP for customer aircon-cleaning appointments and administrative scheduling, payment, service, and customer management.

## Local URLs

- Customer website: `http://127.0.0.1:8000`
- Admin dashboard: `http://127.0.0.1:8000/admin`

## Demo admin

- Email: `admin@icybreeze.test`
- Password: `password`

Change these credentials before using the system outside a local demo.

## Included MVP features

- Branded public website with services, process, coverage, FAQ, and contact pages
- Four-step guest booking flow with live price summary
- One-active-appointment-per-time-window conflict protection
- Secure private booking management link and 24-hour cancellation policy
- Cash-after-service payment records
- Safe PayMongo-style demo payment records without collecting card details
- Admin authentication, dashboard metrics, appointment filters/details/status/rescheduling
- Payment reconciliation, service management, and customer directory
- Seeded SQLite demo data and automated feature tests

## Run locally

```powershell
cd C:\xampp\htdocs\SchedulingSystem
C:\xampp\php\php.exe artisan migrate:fresh --seed
npm install
npm run build
C:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8000
```

## Run with Docker

Docker packages Apache, PHP 8.3, Composer dependencies, production Vite assets, and the SQLite database into one reproducible local stack.

```powershell
docker compose up --build -d
```

Open `http://127.0.0.1:8080`. The first start creates a persistent application key and SQLite database, runs migrations, and loads the demo records. Data remains available across container restarts in named Docker volumes.

Useful commands:

```powershell
docker compose logs -f app
docker compose down
docker compose down -v # Also removes the Docker database and generated app key.
```

Set another host port when `8080` is already in use:

```powershell
$env:DOCKER_APP_PORT=8081
docker compose up --build -d
```

## Test

```powershell
C:\xampp\php\php.exe artisan test
```

The online-payment choice is deliberately a demo workflow. Add live PayMongo credentials, signed webhooks, idempotency handling, and production secrets before enabling real payments.
