# Changelog

All notable changes to this package are documented here.

## [Unreleased]

## [1.0.6] - 2026-10-04

### Fixed
- `RequireRole` middleware now returns proper JSON responses (`401`/`403`) for
  API/JSON requests instead of an HTML redirect. Previously, every unauthorized
  API call received a `302` redirect, which mobile/PWA clients could not handle.
  Web requests keep their original redirect behavior.
- `RequireRole::unauthorized()` no longer calls `$request->route()->getAction()`
  on a null route (could throw when no route matched).

### Added
- Regression tests for `RequireRole` covering both web (redirect) and API
  (JSON 401/403) behavior.

- Standardized package metadata, validation behavior, and security documentation.
