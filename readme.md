## Curl

[![Latest Stable Version](https://poser.pugx.org/jyggen/curl/version.png)](https://packagist.org/packages/jyggen/curl) [![Total Downloads](https://poser.pugx.org/jyggen/curl/d/total.png)](https://packagist.org/packages/jyggen/curl) [![Build Status](https://travis-ci.org/jyggen/curl.png)](https://travis-ci.org/jyggen/curl)

A lightweight cURL library with support for multiple requests in parallel.

[Find Curl on Packagist/Composer](https://packagist.org/packages/jyggen/curl)

### Documentation

Documentation for this library is available at [docs.jyggen.com](http://docs.jyggen.com/curl).

### About

#### Requirements

* PHP 5.3 or above.
* Curl extension (obviously).

#### Bugs and Feature Requests

Please create an issue or pull request on [GitHub](https://github.com/jyggen/curl).

#### Author

Jonas Stendahl ([@jyggen](http://twitter.com/jyggen))  
jonas.stendahl@gmail.com

[See the list of contributors here](https://github.com/jyggen/curl/contributors).

#### License

This library is licensed under the MIT license.


### Changelog

### 3.0.1

* Fixed broken test.
* Code cleanup.

#### 3.0.0

* Renamed `Session` to `Request` (and `SessionInterface` to `RequestInterface`).
* `Curl` will now always return an array with instances of `Response`.
* Changed dependency of `symfony/http-foundation` to `~2.3`.

#### 2.1

* Refactored Curl to be more testable.
* Added more tests to improve coverage.

#### 2.0.2

* Fixed an issue with empty responses.

#### 2.0.1

* Fixed many issues with `Response::forge()` by using `CURLINFO_HEADER_SIZE`.
* Changed dependency of `symfony/http-foundation` from `2.2.*` to `~2.2`.
* Removed unused excepetions.
* Improved documentation of the code.

#### 2.0.0

Version 2.0 introduces a new library flow which changes the way `Dispatcher` and `Session` interacts with each other. If you've only used the static helper `Curl` in the past these changes shouldn't affect you that much. `Dispatcher` is stripped down to only be a wrapper around `curl_multi_init()` while `Session` continues to wrap around `curl_init()` but with more functionality previously located in `Dispatcher`.
