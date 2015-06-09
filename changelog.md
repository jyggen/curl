# Changelog
All notable changes to this library will be documented in this file.

## 4.0.0 - *unreleased*

### Added
- `all()` to `Dispatcher` to retrieve all requests attached to it.
- `getStackSize()` to `Dispatcher` to retrieve the current stack size.
- `setStackSize()` to `Dispatcher` to set its stack size.
- Support for `CurlFile` to `post()` and `put()` on `Curl`. [GH-15]

### Deprecated
- Calling `get()` on `Dispatcher` without a key. Use `all()` on `Dispatcher` instead.

### Changed

- Moved to the namespace `Jyggen\Curl`.
- Migrated to PSR-4 autoloading.
- Moved `Curl` from `jyggen\Curl` to `Jyggen\Curl\Curl`.
- Requests added to the dispatcher are now split into stacks to avoid a lot of simultaneously requests.
- A closure can be passed to `execute()` on `Dispatcher`. It'll be used as a callback for each response. 
- `__destruct()` on `Request` will now close the internal cURL resource.
- The library now depends on `^2.0.5` of `symfony/http-foundation`.

### Improved
- Refactored `Curl` away from `__callStatic()` to make the public class API more obvious.
- Improved the PUT support on `Curl`.
- Travis CI testing.

## 3.0.1 - 2013-08-21

### Changed
- The library now depends on `~2.0` of `symfony/http-foundation`.

### Improved
- Travis CI testing.
- Cleaned up the code a bit.

### Fixed
- Broken tests.

## 3.0.0 - 2013-06-11

### Added
- More unit tests.

### Changed
- Renamed `Session` to `Request`.
- Renamed `SessionInterface` to `RequestInterface`.
- The library now depends on `~2.3` of `symfony/http-foundation`.

### Improved
- Refactored `Curl` to be more testable.

## 2.0.2 - 2013-04-24

### Fixed
- An issue with empty responses.

## 2.0.1 - 2013-04-22

### Removed
- Unused exceptions.

### Changed
- The library now depends on `~2.2` of `symfony/http-foundation`.

### Improved
- Improved code documentation.

### Fixed
- Many issues with `forge()` on `Response` by using `CURLINFO_HEADER_SIZE`.

## 2.0.0 - 2013-03-26

Version 2.0 introduces a new library flow which changes the way `Dispatcher` and `Session` interacts with each other. If you've only used the static helper `Curl` in the past these changes shouldn't affect you that much. `Dispatcher` is stripped down to only be a wrapper around `curl_multi_init()` while `Session` continues to wrap around `curl_init()` but with more functionality previously located in `Dispatcher`.

## 1.0.2 - 2013-01-10

### Added
- `getSessions()` on `Dispatcher` to retrieve all currently added sessions.
- Unit tests for `Dispatcher`.

### Improved
- Cleaned up the code a bit.

## 1.0.1 - 2013-01-01

### Added
- Unit tests for `Session`.

### Fixed
- An issue with `setOption()` on `Session` not handling array input correctly.

## 1.0.0 - 2012-12-20
