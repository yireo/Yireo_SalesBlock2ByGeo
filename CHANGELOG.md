# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [3.0.0] - 25 November 2026
### Added
- Three-letter country codes and continent rules now work with a GeoIP2 database, which is preferred over GeoIP v1
- Admin setting for trusted proxies
- New `%country%` variable for the rule message
- PHP 8.5 support
- Documentation for geo patterns, GeoIP2 setup and trusted proxies

### Changed
- BREAKING: continent rules need the `continent:` prefix (for instance `continent:EU`), a bare two-letter code always means a country
- BREAKING: the rule message variable `%geo%` is renamed to `%ip%`, and the customer message no longer reveals the matched geo pattern (it is logged instead)
- BREAKING: `GeoMatcher::matchByTwoLetterContinentCode()` is replaced by `matchByContinentCode()`
- GeoIP settings are now global only
- Composer and module dependencies match actual usage (Magento_Customer and Magento_Quote are no longer required)

### Fixed
- Geo lookup errors (unknown or private IPs, corrupt databases, TypeErrors from the legacy extension) no longer break the cart or checkout
- An unknown location no longer matches every country rule
- Error in the database path lookup, caused by calling getDriver() on a read interface
- Deprecated setTemplate() call in the admin GeoIP check block

### Security
- Geo rules could be bypassed by faking the X-Forwarded-For header; the client IP now comes from REMOTE_ADDR and X-Forwarded-For is only followed for configured trusted proxies
- The GeoIP database path is restricted to a readable .mmdb file inside var/ (no path traversal or symlinks out of var/)
- Output in the admin GeoIP check template is escaped

### Removed
- PHP 8.1 support

## [2.1.2] - 9 April 2024
### Fixed
- Fix PHP8 class renaming

## [2.1.1] - 10 August 2022
### Fixed
- Fix incompatible driver interface

## [2.1.0] - 16 April 2022
### Fixed
- Remove PHP dep from composer
- PHP 8 compatibility (and therefore Magento 2.4.4)

## [2.0.3] - 10 March 2021
### Added
- Add admin setting to check for GeoIP
- Composer requirement for PHP GeoIP module

### Removed
- Removed unneeded module version

## [2.0.2] - 9 March 2021
### Fixed
- Broken namespace dependencies
- Remove PHP dep from composer

## [2.0.1] - 29 July 2020
### Added
- Magento 2.4 compatibility

## [2.0.0] - July 2019
### Added
- Initial public release
