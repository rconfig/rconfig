# Changelog

All notable changes to rConfig v8 Core are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [8.2.16] - 2026-08-16

### Fixed
- Template values were consumed raw at each point of use, so every consumer invented its own comparison and the comparisons disagreed. Symfony's YAML parser types `on`, `off`, `yes` and `no` as strings but types `true` and `false` as real booleans, so one authoring mistake produced opposite outcomes: `enable: true` still turned enable mode on because the comparison was loose, while `paging: true` silently stopped disabling the pager, which surfaces as configuration output truncated at the pager with nothing logged. Template switches and `connect.protocol` are now normalised once, immediately after the YAML parse, so booleans, `yes` / `no`, `true` / `false`, casing and stray whitespace all settle on the same values. `protocol: SSH` now dispatches instead of falling through to a generic "template file could be invalid" exception, and that exception now names the offending value. A value nobody recognises keeps its current off by default behaviour and is logged as a warning rather than guessed at.
- The telnet read loop interpolated the device prompt straight into a regular expression without escaping it, so a prompt carrying the pattern delimiter, such as `admin@sw1/config#` or a MikroTik `[admin@MikroTik] /interface>`, closed the pattern early. Every match then failed and the loop read until the socket died instead of matching. The prompt is now escaped once per read, falls back to a fully quoted literal if it still will not compile, and the buffer tail is compared literally first so a prompt containing regex metacharacters matches as typed.
- `dropFirstAndLastLinesFromArray` strips the echoed command from the front of a command result and the trailing prompt from the back. Both are positional guesses, and on a result of fewer than three lines the strip consumed the entire output, persisting an empty configuration as a successful backup. Short results are now left untouched. Applies to both SSH and Telnet.
- A telnet port that was null, empty or outside 1 to 65535, whether from the template or a device level override, was dialled as written because the validation helper returned the original value from both of its branches and its result was never assigned. Such ports now fall back to the telnet default of 23.
- The template `setTerminalDimensions` value was read from a dynamic property on the phpseclib connection object, which PHP 8.2 deprecated, so it never sized the ANSI screen. It is now read from the connection object this application owns, and a missing or malformed value leaves the ANSI default in place.
- Every rConfig-templates URL in the demo and test data seeders pointed at the pre restructure layout of the templates repository, which has moved to a lowercase hyphenated convention with no redirect stubs left behind. All eight URLs now resolve. The Palo Alto entry used percent encoded spaces against a directory that used underscores, so that one had never resolved in any release.
- The demo template seeder paired the SonicWall filename with the PAN-OS URL, duplicating the PAN-OS entry while shadowing the real SonicWall pair. Because the seeder skips a file that already exists, the SonicWall demo template was seeded with Palo Alto content. The redundant entry is removed and every filename and URL pair is now unique.

- The test suite rebuilds its schema with `migrate:fresh`, which drops every table, once per test process, while every run shares a single test database. Two overlapping runs destroyed each other, and the second run dropping tables under the first surfaced as missing tables, tables missing the columns their later migrations add, and lock wait timeouts scattered across unrelated tests. A run now holds a database level lock for its lifetime, so a competing run waits and then reports the collision instead of corrupting both. Affects the test suite only.
- Test expectations that still described the templates repository before its restructure, covering the default branch name and the capitalised vendor directories and file names. The behaviour they were asserting against was already correct.

### Added
- `vt100` is a recognised template section. It carries comment mappings for `hasSplashScreen`, `hasSplashScreenEnterKey`, `splashScreenReadToText` and `splashScreenSendControlCode`, and it is emitted last in the section order.

### Changed
- The template reformatter keeps the raw body of every section, so a section it cannot parse as flat key and value pairs, such as one using nested keys or list items, is carried through verbatim rather than dropped. It also now accepts CRLF and CR line endings.

### Deprecated
- The template keys `auth.hpAnyKeyPrmpt`, `config.linebreak`, `config.pagerPrompt` and `config.pagerPromptCmd` are formally deprecated. Nothing read them, and they are no longer loaded. Templates carrying them continue to parse and connect unchanged.

## [8.2.15] - 2026-08-13

### Fixed
- Docker installs using the optional `storage` bind mount were never seeded from the image, because Docker masks image content under a bind mount and the entrypoint recreated only part of the tree. `storage/app/rconfig` and the five bundled template files were absent, giving a 500 on template import and on opening a template, and silent failures writing task reports. The image now ships a storage skeleton that the entrypoint restores missing paths from, without overwriting existing data. Named volume installs were never affected. Present since Docker images were introduced in 8.2.7. Reported in #357.
- Template import creates its target directory recursively, and task reports create the report directory when absent. Both also apply to bare metal installs with an incomplete storage tree, such as a restored backup.

### Changed
- `GET /api/templates/{id}` returns 200 with empty `code` and `fileMissing: true` when the file behind a template record is missing, rather than a 500. Clients reading `code` should check `fileMissing` first.

## [8.2.14] - 2026-08-13

Security release. Upgrade is recommended for all 8.x installations. **Run `php artisan rconfig:set-config-permissions` once after upgrading**, because the permission changes below apply to newly written configurations only and configurations already on disk keep the permissions they were written with.

### Security
- Downloaded device configurations were written world readable (`0444`) inside world traversable directories (`0755`), so any unprivileged local account on the host could read every stored configuration, including the secrets they contain such as VPN pre shared keys, RADIUS secrets and SNMP community strings. Because the application set these modes with an explicit `chmod`, a hardened host umask was overridden on every write. Configs are now written `0440` in `0750` directories, and the brief window while contents are written is `0660` rather than `0666`. Reported in #354.
- The temporary copies of configuration content created during a config compare were written `0644` into a `0777` directory, exposing the same content by another route. Both are now created with the configured config modes.
- The Docker image and container entrypoint applied `chmod -R 775` across the whole `storage` tree, which re-granted "other" read and traverse on the config data directory on every build and every container start. Both are now scoped to the framework, log and bootstrap cache directories.

### Added
- `rconfig:set-config-permissions` re-applies the configured modes to configurations already on disk. **Existing installations must run this once after upgrading**, because the code change only affects newly written files and historic configs keep their original permissions. Supports `--dry-run` and `-v`.
- `RCONFIG_CONFIG_FILE_MODE` and `RCONFIG_CONFIG_DIR_MODE` for environments where the web server and the queue worker run as users that share no common group. The secure defaults are `0440` and `0750`.

### Fixed
- A stray `dump()` in the configuration save path wrote to standard output from queue workers when a download produced no configuration. The failure was already recorded in the activity log.
- The root `VERSION` file was left at `8.2.12` when `8.2.13` was released. It is now covered by a test asserting it matches `composer.json` and `config/app.php`, and by a release workflow job that fails the build if a pushed `core-*` tag disagrees with any of the three.

## [8.2.13] - 2026-08-10

Security release. Upgrade is recommended for all 8.x installations.

### Security
- The device credentials endpoints on the v1 REST API returned `cred_password` and `cred_enable_password` in cleartext. Both fields are now masked on the way out. Masking is unconditional on the external API: the `MASK_DEVICE_CREDENTIALS` setting governs the device endpoints only and can no longer re-enable cleartext secrets for a token authenticated caller.
- The export download endpoint built its path by concatenating the raw `filename` query parameter onto the export directory, so a request could walk out of that directory and read any file readable by the web user. The filename is now reduced to a basename and the resolved path confirmed to be a real file inside the export directory.
- Updating a template deleted the outgoing file using the `fileName` value from the request body, so any authenticated user could nominate an arbitrary writable file for deletion, including `.env` and stored configuration history. The outgoing name is now taken from the persisted template record and reduced to a basename, and traversal sequences in `fileName` are rejected at the form request.
- The `/api/users` resource acted on arbitrary accounts but was reachable by any authenticated user, and the store request only validated the role being written. A standard user could reset an administrator's password while submitting the `User` role, then sign in as that account. Listing, viewing and deleting users and approving SSO accounts were equally open. The resource now requires an administrator, enforced by new middleware and by an independent check in the form request.
- Self service account endpoints resolve the account from the session rather than the `{userid}` in the URL, so a caller can only affect their own record. The route segments are retained so existing clients keep working. Changing your own password still requires the current one.
- The template repository browser accepted an absolute path from the caller and globbed it, so the endpoints listing folder contents and reading template files could enumerate and read files outside the cloned templates repository. Both now resolve the supplied path through a containment check against the repository directory, and file reads are restricted to `.yml` and `.yaml`.
- Report retrieval concatenated the supplied id onto the report directory. A report id is a UUID, so the id is now validated as one before the path is built, and the resolved path is confirmed to sit inside the report directory.

### Added
- `GET /api/user/profile` returns the signed in user, so the profile screen no longer reaches into the administration endpoints.
- `PathContainmentService` for resolving a file or directory and confirming it sits inside an expected root. Used by the export download, template repository and report endpoints.
- `SECURITY.md`, so vulnerability reports have a documented private channel.
- Health check email notification settings in `.env.example`. Thanks to @vitor-ao.

### Changed
- The health check mail configuration reads from an environment variable rather than a hardcoded value. Thanks to @vitor-ao.

### Fixed
- Field widths in the templates import dialog.
- Reading a malformed template file returned a 500 whose exception message embedded a snippet of the file. The parse is now handled and the failure logged.

### Removed
- `TaskReportController::getReport()`, which duplicated `show()` and was not routed.

## [8.2.12] - 2026-08-07

### Fixed
- The login page is no longer unreadable on a fresh install. It hardcodes a black background, but the dark theme class was only applied once the SPA had loaded, so a browser or operating system set to light mode rendered the form with the light colour palette: near black text on a black page. The theme now resolves to dark before the page paints, and the auth and SPA layouts pin themselves to dark regardless of the system setting.

## [8.2.11] - 2026-08-03

### Fixed
- SSO error messages now name the provider that actually failed. Every failure previously said Microsoft, whichever provider was in use. The SAML2 label comes from the configured display name.
- A failed SSO sign in now shows the specific reason (access denied, missing callback parameters, account not registered) instead of a generic message. The controller was replacing the handler's response with its own fallback.
- The login page now displays messages flashed by the server. The Vue component did not read the prop the view passes it, so SSO callback errors never reached the user.
- Tab icons sit beside their labels rather than above them, as seen on the Standard and Docker tabs of the settings update panel.

### Changed
- Docker release images build each architecture on a runner of that architecture. The arm64 half previously ran under emulation and dominated the build time.
- Test isolation reworked so a test cannot leak an open transaction into the next one, which was causing lock wait timeouts on the shared test database.
- Front end dependencies updated, including js-yaml 5, monaco-editor 0.56, and Pinia 4. js-yaml and monaco-editor both reorganised their module exports in these versions, so the YAML parsing and code editor imports now use the new entry points.

## [8.2.10] - 2026-08-01

### Fixed
- Removed a duplicate `Auth::routes()` call from the web routes. The second, unqualified call re-registered the default authentication routes and reinstated the registration endpoints that the preceding call had disabled.
- The SSO approval check now also applies to the local login form. An SSO account that an administrator has not approved can no longer sign in through it. Local accounts are unaffected.

### Changed
- A user record created without an explicit role now defaults to the `User` role instead of `Admin`. Existing users are unchanged.
- Accounts provisioned through SSO are assigned the `User` role when they are created. Administrators keep their role when signing in.

### Removed
- The unused registration controller. Its view was removed in 8.1 and nothing referenced it.

## [8.2.8] - 2026-07-17

### Changed
- The `role` field on user create and update now validates against a fixed set of allowed values instead of accepting any string.
- Granting the Admin role to a user, including changing your own role, now requires the acting account to already hold the Admin role.
- The device reachability check job now looks up the device by ID when it runs instead of carrying the full device record in the queued job payload.

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
- Initial release of rConfig v8 Core tagged updates: network device configuration backup and versioning, multi vendor templates and commands, SSH and Telnet downloads, scheduled and on demand tasks with run monitoring, Horizon queue monitoring, a REST API with a Vue 3 SPA front end, and SSO via Socialite.
