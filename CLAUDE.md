# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**rConfig V8 Core (`v8core`)** is the core edition of the rConfig network device configuration management platform. It is a Laravel 12 application that connects to network devices, downloads and versions their configurations, and provides day to day device, vendor, and template management. It exposes a REST API (Sanctum) consumed by a Vue 3 single page app.

### Technology stack

- **Backend**: PHP 8.4 (supports 8.2 to 8.4), Laravel 12.
- **Frontend**: Vue 3 single page app, Vite, Tailwind CSS 4, shadcn-vue, Pinia, vue-router, Monaco editor, TypeScript. The SPA is served by `SpaController`. Note the Vite config files are `vite.dev.config.js` and `vite.prod.config.js` (there is no `vite.config.js`).
- **Database**: MySQL 8 / MariaDB (SQLite or a dedicated `test_mysql` connection for tests).
- **Queue / cache**: Redis (`predis/predis`) with Laravel Horizon.
- **Device connectivity**: `phpseclib/phpseclib` (SSH / Telnet).
- **API**: REST controllers under `app/Http/Controllers/Api`, authenticated with Laravel Sanctum.
- **Auth**: Sanctum plus Laravel Socialite for SSO (Google, Microsoft, Okta, SAML2).
- **Config diffing**: `jfcherng/php-diff`. **Excel export**: `maatwebsite/excel`.
- **Spatie packages**: `laravel-activitylog`, `laravel-health`, `cpu-load-health-check`, `laravel-query-builder`. **Log viewer**: `opcodesio/log-viewer`.
- **Testing**: PHPUnit 12.
- **Code quality**: PHPStan via Larastan (level 0, with `phpstan-baseline.neon`) and Laravel Pint.

Always check this project's `composer.json` and `package.json` before assuming a package is available.

### Key features

- Network device configuration backup and versioning.
- Multi vendor support via templates and commands.
- Automated configuration downloads over SSH and Telnet.
- Scheduled and on demand download tasks, with task run monitoring.
- Real time queue monitoring with Laravel Horizon.
- REST API (Sanctum) plus a Vue 3 SPA front end.
- User authentication with SSO via Socialite.
- Activity and user action logging.

## Development commands

### Backend

```bash
composer install
php artisan migrate
php artisan db:seed
php artisan serve

# Clear caches
php artisan optimize:clear
```

### Frontend

```bash
npm install
npm run dev      # development build with hot reload
npm run build    # production build
```

If a front end change is not visible, the user may need to run `npm run dev` or `npm run build`.

### Testing

This project uses PHPUnit. All tests are PHPUnit classes. If you see a test written for Pest, convert it to PHPUnit.

```bash
php artisan test --compact
php artisan test --compact tests/Unit/ExampleTest.php
php artisan test --compact --filter=testName
```

Test suites are `Unit`, `Fasttests`, and `Slowtests`. `Slowtests` cover real device connections and longer running operations. Shared helpers live in `tests/Traits`. The test database uses a dedicated connection (see `phpunit.xml`).

### Code quality

```bash
vendor/bin/pint --dirty --format agent   # format only the files you changed
composer stan                            # PHPStan via Larastan (see phpstan.neon, level 0)
```

Run Pint on changed PHP before finalizing. Do not run `pint --test`; just run Pint to fix style.

## Architecture overview

### Service layer pattern

The application follows a service oriented architecture:

- **Controllers** (`app/Http/Controllers/`, with API controllers under `Api/`) handle HTTP and delegate to services. Keep them thin.
- **Services** (`app/Services/`) hold business logic. Current service groups: `Config`, `Device`, `SocialAuth`, `Templates`, `UserLog`, `Utilities`.
- **Models** (`app/Models/`) are Eloquent models. Core domain models include `Device`, `DeviceModel`, `DeviceCredentials`, `DeviceComment`, `Vendor`, `Category`, `Command`, `Template`, `Config`, `ConfigSummary`, `Task`, `Taskdownloadreport`, `Tag`, `TrackedJob`, `MonitoredScheduledTasks`, plus `User`, `Setting`, `Banner`, `Notification`, and activity / integration models.
- **Jobs** (`app/Jobs/`) run long operations on the queue, for example `DeviceDownloadJob`, `DownloadConfigNowJob`, `TaskDownloadRun`, `CheckDeviceReachabilityJob`, `PurgeFailedConfigsJob`.
- Other directories: `Casts`, `Console`, `CustomClasses`, `DataTransferObjects`, `Enums`, `Exceptions`, `Exports`, `HealthChecks`, `Notifications`, `Observers`, `Providers`, `Rules`, `Traits`.

### Configuration download flow

1. A download is started (manually, via the API, or by a scheduled task).
2. The system selects the vendor template for the device.
3. An SSH or Telnet connection is established with phpseclib.
4. Template commands run to retrieve the configuration.
5. The config is stored and versioned, and a `Config` / `ConfigSummary` record is created.
6. Events and notifications fire for logging and reporting.

## Database

Migrations live in `database/migrations/`. Seeders live in `database/seeders/`, with test data seeders under `database/seeders/testdata/`. The schema covers devices, device models, credentials, vendors, categories, commands, templates, configs, tasks, tags, users, settings, and notifications.

When modifying a column in a migration, include all attributes the column previously had, or they will be dropped.

## Conventions

### Code style and PHP

- Follow existing conventions. When creating or editing a file, check sibling files for structure, approach, and naming.
- Use Laravel Pint for formatting. Larastan (PHPStan) must pass; see `phpstan.neon` and `phpstan-baseline.neon`.
- Always use curly braces for control structures, even single line bodies.
- Use PHP 8 constructor property promotion. Do not leave empty zero parameter constructors.
- Use explicit return types and parameter type hints on every method.
- Prefer PHPDoc blocks over inline comments, and use array shape definitions in PHPDoc.
- Use descriptive names, for example `isRegisteredForDiscounts`, not `discount()`.

### The Laravel way

- Use `php artisan make:` commands to create migrations, controllers, models, and so on. Pass `--no-interaction`.
- For generic classes use `php artisan make:class`.
- When creating a model, also create a factory and a seeder for it.
- For APIs, default to Eloquent API Resources and follow the existing API conventions in `app/Http/Controllers/Api`.
- Prefer named routes and the `route()` function for links.
- Casts should be set in a `casts()` method on the model, following existing models.

### Laravel 12 structure

This project uses the Laravel 10 style file structure (it upgraded from Laravel 10 and did not migrate to the streamlined layout, which is fine). So middleware registration is in `app/Http/Kernel.php`, console commands and the schedule register in `app/Console/Kernel.php`, and there is no `bootstrap/app.php` application config. Do not migrate the structure unless the user asks.

### Testing rules

- Every change must be tested. Add or update a test, then run it.
- Most tests should be feature or integration tests. Use factories and their custom states for test data.
- Run the minimum set of tests with a filter before finalizing. When your feature's tests pass, ask the user if they want the full suite run.
- Do not remove tests or test files without approval.

### Documentation and replies

- Only create documentation files when the user explicitly asks.
- Humanise written content. Do not use em dashes in body copy. Use a period, comma, parentheses, or colon instead. The hyphen is fine in compound terms like `Cisco-Edge`.
- Be concise. Focus on what matters rather than explaining the obvious.

## Notes for AI assistants

- This is a Laravel 12 application using modern PHP 8 features. Prefer Laravel conventions and built in features.
- Respect the service layer. Keep controllers thin and put long running work on the queue.
- Verify a package exists in this project's `composer.json` or `package.json` before using it.
- Network device connections handle sensitive infrastructure. Handle errors gracefully and keep security in mind.

<!--
Local, non public guidance (edition boundary, Pro comparison notes) lives in CLAUDE.local.md,
which is gitignored. The import below loads it when present and is a no-op for public clones.
-->
@CLAUDE.local.md
