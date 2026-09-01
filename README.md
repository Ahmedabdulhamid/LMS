# LMS

A modern Laravel-based learning management system built with Filament admin tooling, Vite assets, queue-driven background processing, and cloud storage for private course media.

## Overview

This project provides a complete LMS experience for online education delivery, including course management, enrollment flows, payment handling, student progress tracking, video processing, and a Filament-powered admin backend.

## Features

- Course catalog and content management
- Instructor and student roles
- Video lesson delivery with secure/private media handling
- HLS video processing pipeline for adaptive streaming
- Enrollment and purchase workflows
- Payment integration support (Paymob-ready configuration)
- Student progress and completion tracking
- Admin dashboard using Filament
- Queue-based processing for video and payment workflows
- Vite + Tailwind frontend asset pipeline

## Tech Stack

- Laravel 13
- PHP 8.3+
- Filament 5
- Livewire
- Vite
- Tailwind CSS
- MySQL / SQLite-compatible Laravel database configuration
- Cloud storage via Laravel Filesystem with S3/R2 support
- Redis-ready configuration
- Queue workers and scheduler support

## Requirements

- PHP 8.3 or newer
- Composer
- Node.js 18+ and npm
- SQLite for local development or MySQL for production
- FFmpeg and FFprobe for video processing
- Optional: Redis and a queue worker environment

## Project Structure

- `app/` — Laravel application code and domain logic
- `app/Filament/` — admin panels and resources
- `app/Models/` — Eloquent models
- `config/` — configuration files
- `database/` — migrations and seeders
- `public/` — public web root and compiled assets
- `resources/` — frontend templates and CSS/JS source files
- `routes/` — web and API routes
- `storage/` — logs, cached files, and uploaded content
- `tests/` — automated tests

## Installation

1. Clone the repository:

```bash
git clone https://github.com/Ahmedabdulhamid/LMS.git
cd LMS
```

2. Install PHP dependencies:

```bash
composer install
```

3. Install front-end dependencies:

```bash
npm install
```

4. Copy the environment example file:

```bash
cp .env.example .env
```

5. Generate the application key:

```bash
php artisan key:generate
```

## Environment Setup

Update your `.env` file with your local or server environment values, especially:

- `APP_NAME`
- `APP_ENV`
- `APP_URL`
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `QUEUE_CONNECTION`
- `FILESYSTEM_DISK`
- `AWS_*` or `R2_*` values for cloud storage
- `PAYMOB_*` values for payment integration
- `FFMPEG_PATH` and `FFPROBE_PATH`

Important: do not commit your `.env` file to version control. Keep secrets in your local environment or deployment secret manager.

## Database Setup

For local SQLite development:

```bash
php artisan migrate
```

For MySQL or another supported database, configure the environment variables in `.env` and then run:

```bash
php artisan migrate
```

Optional seeding:

```bash
php artisan db:seed
```

## Storage and Public Assets

Create the storage symlink if needed:

```bash
php artisan storage:link
```

This project supports private media storage and generated public links for uploaded assets. For video processing, FFmpeg and FFprobe must be available and configured in `.env`.

## Running the Application

Start the Laravel app:

```bash
php artisan serve
```

Start Vite for frontend assets:

```bash
npm run dev
```

Build production assets:

```bash
npm run build
```

## Queue Workers

The app uses Laravel queues for payment and media processing tasks.

Example:

```bash
php artisan queue:work --queue=payments,video-processing,default --tries=5 --timeout=22000
```

## Scheduler

To run scheduled tasks locally:

```bash
php artisan schedule:run
```

For production, add the scheduler to your system cron:

```bash
* * * * * cd /path/to/LMS && php artisan schedule:run >> /dev/null 2>&1
```

## Development Commands

```bash
php artisan test
php artisan optimize
php artisan cache:clear
php artisan config:clear
composer dump-autoload
```

## Deployment Notes

- Set `APP_ENV=production` and `APP_DEBUG=false` in production.
- Configure robust database, queue, cache, and storage settings.
- Ensure FFmpeg and FFprobe are installed on the server.
- Secure your `.env` file and do not expose secret values in Git history.
- Ensure your storage bucket remains private and CORS policies are configured correctly for signed media URLs.
- Run migrations and queue workers after deployment.

## Security

This repository intentionally excludes the following from Git tracking:

- `.env`
- `.env.*`
- `vendor/`
- `node_modules/`
- storage logs and sensitive runtime files
- private certificates and keys

## License

This project is intended for the repository owner and is provided under the project’s existing license terms.
