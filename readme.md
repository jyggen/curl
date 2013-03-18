# Curl [![Build Status](https://secure.travis-ci.org/jyggen/curl.png?branch=master)](https://travis-ci.org/jyggen/curl)

A lightweight cURL library with support for multiple requests in parallel.

[Find Curl on Packagist/Composer](https://packagist.org/packages/jyggen/curl)

## Documentation

Documentation for this library will be available at [docs.jyggen.com](http://docs.jyggen.com/curl) when version 2.0 is fully released.

## About

### Requirements

* PHP 5.3 or above.
* Curl extension (obviously).

### Bugs and Feature Requests

Please create an issue or pull request on [GitHub](https://github.com/jyggen/curl).

### Author

Jonas Stendahl ([@jyggen](http://twitter.com/jyggen))  
jonas.stendahl@gmail.com

[See the list of contributors here](https://github.com/jyggen/curl/contributors).

### License

This library is licensed under the MIT license.


## Changelog

### 2.0.0-RC1

* Added support for headers to `Session`.
* The library now requires `ext-curl`, if that wasn't obvious enough.
* Refactored `Dispatcher::execute()` into using `Dispatcher::process()` internally.
* Refactored all `Curl` public methods into `__callStatic`.
* `InvalidArgumentException` now extends the native SPL extension with the same name.
* `Curl::makeRequest()` should set `CURLOPT_INFILESIZE` to number of bytes (thanks [alixaxel](https://github.com/alixaxel)).
* Fixed various issues with  POST/PUT data in `Curl::makeRequest()`.
* Fixed an issue in `Response::forge()` where headers would be treated as content..
* Removed a lot of unnecessary methods from `DispatcherInterface` and `SessionInterface`.
* Removed destructors from `Dispatcher` and `Session`.
* Added more unit tests (99% coverage).
* Overall cleaner and better code.

### 2.0.0-BETA2

* Made exceptions autoloadable and moved them to the `jyggen\Curl\Exception` namespace.
* `Session` will now close the cURL resource during shutdown.
* Removed `Curl::getDispatcher()`.
* Removed `Curl::getSession()`.
* Removed `Curl::setDispatcher()`.
* Removed `Curl::setSession()`.
* `Dispatcher::get()` with an unknown key now returns `null`.
* Fixed the workaround for [PHP Bug #61141](https://bugs.php.net/bug.php?id=61141).
* Added more unit tests.
* Overall cleaner and better code.

### 2.0.0-BETA1

Version 2.0 introduces a new library flow which changes the way `Dispatcher` and `Session` interacts with each other. If you've only used the static helper `Curl` in the past these changes shouldn't affect you that much. `Dispatcher` is stripped down to only be a wrapper around `curl_multi_init()` while `Session` continues to wrap around `curl_init()` but with more functionality previously located in `Dispatcher`.