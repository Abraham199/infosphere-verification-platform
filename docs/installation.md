# Installation Guide

## Prerequisites

- PHP 8.3+
- Composer
- Node.js and npm
- MySQL 8+

## Setup

1. Copy `.env.example` to `.env`.
2. Configure database credentials.
3. Run `composer install`.
4. Run `npm install`.
5. Run `php artisan key:generate`.
6. Run `php artisan migrate --seed`.
7. Run `npm run build`.

## Deployment Notes

- Set `APP_ENV=production`.
- Set `APP_DEBUG=false`.
- Use HTTPS.
- Configure cron to run Laravel scheduler every minute.
- Protect `.env` and storage directories.
- Use `php artisan config:cache`, `route:cache`, and `view:cache` after deployment.
