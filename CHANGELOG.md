# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [UNRELEASED]

## [2.6.0] - 2025-05-26

### Added

- Handle multiple SCCM configuration
- Add `collection` scope
- PHPUnit test suite for XML generation and query building logic

### Fixed

- Reduce MSSQL connections from N*8 per device to 2 per config by sharing a single connection across all per-device data fetching

## [2.5.1] - 2025-10-07

Compatible with GLPI 11.0.x

### Fixed

- Fix config page error on display
- Fix `password` value not saved
- Drop `mssql_connect` prerequisites
- Fix option ```verify_ssl_cert``` not working for connections

## [2.5.0] - 2025-10-01

### Added

- GLPI 11 compatibility

## [2.4.4] - 2025-09-24

Compatible with GLPI 10.0.x

### Added

### Fixed

- Fix option ```Use LastHWScan``` that no longer worked.
