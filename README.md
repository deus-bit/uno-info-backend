# uno-info-backend

This project is a Laravel-based web application backend, providing API services including user authentication via Laravel Sanctum and a system for managing user permissions.

## Setup

To set up the project for the first time, run the following command:

```bash
composer setup
```

This command will:
1.  Install PHP dependencies via Composer.
2.  Copy `.env.example` to `.env` if it doesn't exist.
3.  Generate an application key.
4.  Run database migrations (seeders are not run by this command).
5.  To run database seeders, execute: `php artisan db:seed`
6.  Install Node.js dependencies via npm.
6.  Build frontend assets.

## Running the Development Server

To start the development server, queue listener, and Vite for asset compilation, use:

```bash
composer dev
```

This will typically make the API accessible on `http://127.0.0.1:8000` (or a similar local address).

## Running Tests

To execute the project's test suite, use the following command:

```bash
composer test
```

This command clears the configuration cache and then runs PHPUnit tests.

## Code Style

The project uses `Laravel Pint` for enforcing code style.

## API Endpoints

*   `/api/user`: Returns the authenticated user's information (requires `auth:sanctum`).
*   `/api/users/{id}/permissions`: Retrieves permissions for a specific user, handled by `PermissionController`.
