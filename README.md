# Job Platform Backoffice

Admin dashboard for the Job Platform.

This repository is responsible for:

- Managing administrators, companies, job categories, vacancies, resumes, and job applications.
- Creating the shared database structure.
- Running all database migrations and seeders.
- Providing the admin interface for the platform.

## Related Repositories

| Repository | Purpose |
|---|---|
| [`job-backoffice`](https://github.com/workAIme/job-backoffice) | Admin dashboard and database setup |
| [`job-user-app`](https://github.com/workAIme/job-user-app) | Public user application |
| [`job-shared`](https://github.com/workAIme/job-shared) | Shared Composer package containing common models and logic |

The backoffice and user application must use the **same database**.

## Requirements

Make sure the following tools are installed:

- PHP 8.2 or later
- Composer
- MySQL or MariaDB
- Node.js and npm
- Git

XAMPP can be used for PHP, MariaDB, and phpMyAdmin.

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/workAIme/job-backoffice.git
cd job-backoffice
```

### 2. Install PHP dependencies

```bash
composer install
```

Composer will automatically install the shared package:

```text
job/shared ^1.0
```

### 3. Create the environment file

On Git Bash, macOS, or Linux:

```bash
cp .env.example .env
```

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

### 4. Generate the application key

```bash
php artisan key:generate
```

### 5. Create the database

Create a new MariaDB/MySQL database using phpMyAdmin or the command line.

Recommended database name:

```text
job_backoffice
```

Then update the database settings in `.env`:

```env
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=job_backoffice
DB_USERNAME=root
DB_PASSWORD=
```

The `job-user-app` repository must use the same database settings.

## Database Migrations

All project migrations are located in:

```text
database/migrations
```

The migrations create the following main tables and supporting tables:

- Users
- Cache
- Queue jobs
- Job categories
- Companies
- Resumes
- Job vacancies
- Job applications
- Analytics fields

Run the migrations from this repository only:

```bash
php artisan migrate
```

## Seed Demo Data

The main seeder is located at:

```text
database/seeders/DatabaseSeeder.php
```

It creates:

- A demo administrator account
- Job categories
- Companies and company owners
- Job vacancies
- Job seekers
- Resumes
- Job applications

The demo data is loaded from:

```text
database/data/job_data.json
database/data/job_applications.json
```

Run migrations and seeders together:

```bash
php artisan migrate --seed
```

### Demo Administrator

```text
Email: admin@admin.com
Password: 12345678
```

> Important: These credentials are for local development only. Change the password before using the project in a real environment. Do not run the demo seeder in production.

To rebuild the local database completely:

```bash
php artisan migrate:fresh --seed
```

> Warning: `migrate:fresh` deletes all existing database tables and data.

## Frontend Assets

Install the frontend dependencies:

```bash
npm install
```

Build the assets:

```bash
npm run build
```

For frontend development with automatic rebuilding:

```bash
npm run dev
```

If PowerShell blocks `npm.ps1`, use:

```powershell
npm.cmd install
npm.cmd run build
```

## Storage Link

Create the public storage link if the project serves uploaded files:

```bash
php artisan storage:link
```

## Run the Backoffice

```bash
php artisan serve --port=8000
```

Open:

```text
http://127.0.0.1:8000
```

## Run the Complete Platform

Run the backoffice first because it creates and seeds the shared database.

Then configure and run `job-user-app` using the same database:

```text
Backoffice: http://127.0.0.1:8000
User app:   http://127.0.0.1:8001
```

## Useful Commands

Clear Laravel caches:

```bash
php artisan optimize:clear
```

Check migration status:

```bash
php artisan migrate:status
```

Run automated tests:

```bash
php artisan test
```

## Environment Notes

The default environment uses database-backed sessions, cache, and queues:

```env
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

The required supporting tables are created by this repository's migrations.

AWS settings are only needed when the application is configured to use Amazon S3. The default filesystem is local.

## Security

Never commit these files or values:

- `.env`
- API keys
- Database passwords
- AWS credentials
- Production secrets

Only `.env.example` should be committed.
