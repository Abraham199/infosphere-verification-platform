# Infosphere Verification Portal

Infosphere Verification Portal (IVP) is a Laravel 12 modular monolith for a commercial multi-tenant SaaS identity verification platform.

This repository currently contains Phase 1: Enterprise Foundation. It deliberately excludes wallet, payments, verification, reporting, notifications, monitoring, affiliate, promotions, support, and developer API business implementations.

## Requirements

- PHP 8.3+
- Composer
- Node.js and npm
- MySQL 8+

## Installation

1. Install PHP dependencies:

   ```bash
   composer install
   ```

2. Install frontend dependencies:

   ```bash
   npm install
   ```

3. Create the environment file:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configure MySQL credentials in `.env`.

5. Run migrations and seeders:

   ```bash
   php artisan migrate --seed
   ```

6. Build assets:

   ```bash
   npm run build
   ```

7. Run the application:

   ```bash
   php artisan serve
   ```

## Development Notes

- Tenant isolation is application-level with shared database tenancy.
- Tenant-aware records must include `tenant_id`.
- Business logic belongs in services, not controllers.
- External integrations must be implemented behind contracts and adapters.
- Financial, wallet, payment, and verification modules are intentionally deferred to later phases.

## Testing

```bash
composer test
```
