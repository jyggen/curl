# Changelog

## 3.0.1

* Improved Travis CI testing.
* Changed dependency of `symfony/http-foundation` to `~2.0`.
* Fixed broken test.
* Code cleanup.

## 3.0.0

* Renamed `Session` to `Request` (and `SessionInterface` to `RequestInterface`).
* `Curl` will now always return an array with instances of `Response`.
* Changed dependency of `symfony/http-foundation` to `~2.3`.

## 2.1

* Refactored Curl to be more testable.
* Added more tests to improve coverage.

## 2.0.2

* Fixed an issue with empty responses.

## 2.0.1

* Fixed many issues with `Response::forge()` by using `CURLINFO_HEADER_SIZE`.
* Changed dependency of `symfony/http-foundation` from `2.2.*` to `~2.2`.
* Removed unused excepetions.
* Improved documentation of the code.

## 2.0.0

Version 2.0 introduces a new library flow which changes the way `Dispatcher` and `Session` interacts with each other. If you've only used the static helper `Curl` in the past these changes shouldn't affect you that much. `Dispatcher` is stripped down to only be a wrapper around `curl_multi_init()` while `Session` continues to wrap around `curl_init()` but with more functionality previously located in `Dispatcher`.
