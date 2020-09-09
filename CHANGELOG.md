# Change Log
All notable changes to this project will be documented in this file. This project adheres to
[Semantic Versioning](http://semver.org/) and [this changelog format](http://keepachangelog.com/).

## Unreleased

### Changed
- Minimum PHP version is now 7.3.
- Minimum Laravel version is now 8.0.

## [0.4.0] - 2020-09-09

### Added
- Added balance repository.
- The `stripe:query` Artisan command now accepts resource names in either singular or plural form.

### Fixed
- **BREAKING:** The Stripe accounts relationship on the `Connect\OwnsStripeAccounts` trait now correctly
uses the `Contracts\Connect\AccountOwnerInterface::getStripeIdentifierName()` method to determine the
column name on the inverse model. This means the column name now defaults to `owner_id`. This
change could potentially break implementations. If you use a different column from `owner_id`, then
overload the `getStripeIdentifierName()` method on the model that owns Stripe accounts.
- Fixed catching API exceptions in the `stripe:query` Artisan command.

## [0.3.0] - 2020-07-27

### Changed
- Minimum PHP version is now `7.2.5`.
- Minimum Laravel version is now `7.x`.
- Minimum Stripe PHP version is now `7.0`.

## [0.2.0] - 2020-06-17

Release for Laravel `5.5`, `5.6`, `5.7`, `5.8` and `6.x`.

## [0.1.1] - 2020-01-04

### Fixed
- [#3](git@github.com:cloudcreativity/laravel-stripe.git)
Fix facade alias in Composer json.

## [0.1.0] - 2019-08-12

Initial release for PHP 5.6 / Laravel 5.4.
