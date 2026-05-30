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

**Never run `npm run build` or `npm run dev` yourself.** The user always runs these manually. After making front end changes, just tell the user to rebuild; do not invoke either command.

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

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- laravel/framework (LARAVEL) - v12
- laravel/horizon (HORIZON) - v5
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- laravel/socialite (SOCIALITE) - v5
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- phpunit/phpunit (PHPUNIT) - v12
- eslint (ESLINT) - v10
- prettier (PRETTIER) - v3
- tailwindcss (TAILWINDCSS) - v4
- vue (VUE) - v3

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Follow existing application Enum naming conventions.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, ask the user to run `npm run dev`, `npm run build`, or `composer run dev`. Do not run these yourself.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- This project upgraded from Laravel 10 without migrating to the new streamlined Laravel file structure.
- This is perfectly fine and recommended by Laravel. Follow the existing structure from Laravel 10. We do not need to migrate to the new Laravel structure unless the user explicitly requests it.

## Laravel 10 Structure

- Middleware typically lives in `app/Http/Middleware/` and service providers in `app/Providers/`.
- There is no `bootstrap/app.php` application configuration in a Laravel 10 structure:
    - Middleware registration happens in `app/Http/Kernel.php`
    - Exception handling is in `app/Exceptions/Handler.php`
    - Console commands and schedule register in `app/Console/Kernel.php`
    - Rate limits likely exist in `RouteServiceProvider` or `app/Http/Kernel.php`

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>
