# Changelog

All notable changes to rConfig v8 Core are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [8.2.6] - 2026-06-29

### Fixed
- Config Compare now returns results. The single select device field emits an object, but the compare views treated it as an array, so the fetch never fired and the results table never rendered.
- The Config Compare date range picker now scopes results. `getAllById` allows the `created_at_between` filter, and the range is inclusive of configs created at any time on the end date.
- Selecting a config for comparison works again. The row checkbox no longer double toggles against the row click, and the unresolved icon component that broke the results badge and diff toolbar was corrected.
- Filtering one side of the compare view no longer remounts the opposite panel.

## [8.2.5] - 2026-06-27

### Added
- Upgraded Configuration Search: search every stored config with multiple terms using All (AND) or Any (OR) matching, scope by tags, devices, command groups, and commands, set a result limit, and read highlighted match previews inline.

## [8.2.4] - 2026-06-27

### Changed
- Maintenance and stability release.

## [8.2.3] - 2026-06-27

### Added
- External REST API (v1 and v2) with token based authentication, API key management, and built in endpoint documentation under Settings.

## [8.2.2] - 2026-06-26

### Added
- Dashboard widgets top navigation button.

### Fixed
- Timezone handling fix (closes #307).

## [8.2.1] - 2026-06-26

### Fixed
- Timezone update fix (closes #307).

## [8.2.0] - 2026-06-26

### Added
- Config Compare and Config Versioning: automatic version tracking with inline and side by side diffs, plus a device config history view.

## [8.1.3] - 2026-06-13

### Changed
- Maintenance and stability release.

## [8.1.2] - 2026-06-09

### Added
- Email notification on device connection and backup failure (RCO-968, #283).

## [8.1.1] - 2026-06-09

### Fixed
- `composer install --no-dev` failure by guarding `ide-helper:generate`.

## [8.1.0] - 2026-05-30

### Added
- Feature and improvement release for rConfig v8 Core.

## [8.0.2] - 2026-05-29

### Fixed
- Post release fixes for the initial v8 Core launch.

## [8.0.1] - 2026-05-29

### Fixed
- Post release fixes for the initial v8 Core launch.

## [8.0.0] - 2026-05-29

### Added
- Initial release of rConfig v8 Core: network device configuration backup and versioning, multi vendor templates and commands, SSH and Telnet downloads, scheduled and on demand tasks with run monitoring, Horizon queue monitoring, a REST API with a Vue 3 SPA front end, and SSO via Socialite.
