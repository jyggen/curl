# Curl [![Build Status](https://secure.travis-ci.org/jyggen/curl.png?branch=master)](https://travis-ci.org/jyggen/curl)

A lightweight cURL library with support for multiple requests in parallel.

[Find Curl on Packagist/Composer](https://packagist.org/packages/jyggen/curl)

## Documentation

Documentation for this library will be available at [docs.jyggen.com](http://docs.jyggen.com/curl) when version 2.0 is fully released. Check below for old pre-2.0 docs.

## About

### Requirements

* PHP 5.3 or above.
* PHPUnit to execute the test suite (optional).

### Bugs and Feature Requests

Please create an issue or pull request on [GitHub](https://github.com/jyggen/curl).

### Author

Jonas Stendahl ([@jyggen](http://twitter.com/jyggen))  
jonas.stendahl@gmail.com

[See the list of contributors here](https://github.com/jyggen/curl/contributors).

### License

This library is licensed under the MIT license.


## Changelog

### 2.0.0-BETA3

* `Curl::makeRequest()` should set `CURLOPT_INFILESIZE` to number of bytes (thanks [alixaxel](https://github.com/alixaxel)).
* The library now requires `ext-curl`, if that wasn't obvious enough.
* Removed a lot of unnecessary methods from `SessionInterface`.

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

### 1.0.2

* Added `Dispatcher::getSessions()`.
* Added 98% test coverage for `Dispatcher`.
* Minor code cleanup.

### 1.0.1
* Fixed an issue with `Session::setOption()` no recursing arrays correctly.
* Added 100% test coverage for `Session`.

## Legacy Documentation

*This documentation is for releases prior to 2.0 and may not be accurate.*

### Static Helpers

This library was created with simplicity in mind, so in most cases you can use the static helpers in the `Curl` class. Each helper will return an array with two indexes: `data` and `info`. `data` will contain the response from your request and `info` anything that can normally be retrieved by `curl_getinfo()`. If multiple URLs are requested the helpers will return an array with a response array for each URL.

#### DELETE

To make a DELETE request you'd use `Curl::delete()`. See `Curl::get()` for usage.

#### GET

To make a GET request you'd use `Curl::get()`. To retrieve a single URL, simply pass it as an argument:

```php
$response = jyggen\Curl::get('http://example.com/');
```

If you want to request multiple URLs you can pass an array to the helper. This will utilize parallel requests (commonly referred to as multi-threaded).

```php
$responses = jyggen\Curl::get(array('http://example.com/', 'http://example.org/'));
```

#### POST

To make a POST request you'd use `Curl::post()`. This method requires two arguments; the request URL and an array of POST data.

```php
$response = jyggen\Curl::post('http://example.com/', array('username' => 'foo', 'password' => 'bar'));
```

For multiple URLs only one argument is needed. This should be an array of POST data with the URL as the key for each index.

```php
$request_info = array(
  'http://www.example.com/' => array('username' => 'foo', 'password' => 'bar'),
  'http://www.example.org/' => array('username' => 'foo', 'password' => 'bar')
);

$responses = jyggen\Curl::post($request_info);
```

#### PUT

To make a PUT request you'd use `Curl::put()`. See `Curl::post()` for usage.

### Session and Dispatcher

For more advanced usage you'll have to go for the classes underneath the helpers. First we have `Session`, which could be referred to as your URL, and then there's `Dispatcher` which keeps track of your sessions and executes your requests.

#### Session

The `Session` object is basically a wrapper around a cURL resource.

* __\_\_construct(string $url)__  
The constructor requires an URL and will initialize a new cURL resource with `CURLOPT_URL` set to it.
* __getHandle()__  
Get the session's cURL resource.
* __getInfo(int $key = null)__  
Get information about the session. A wrapper around `curl_getinfo()`.
* __getResponse()__  
Get the session's response. This is an array with two indexes: `data` and `info`.
* __setOption(mixed $option, mixed $value = null)__  
Set an option for the session. A wrapper around `curl_setopt` and `curl_setopt_array`.
* __setResponse(string $response)__  
Set the session's response. This is used by `Dispatcher` and should probably never be touched by you.


#### Dispatcher

The `Dispatcher` object is your session handler and request executor.

* __addSession(mixed $session)__  
Add a `Session` object (or an array of `Session` objects) to the dispatcher.
* __execute(int $key = null)__  
Execute all or a specific request.
* __getResponses()__  
Retrieve the response of all sessions attached to the dispatcher.
* __getSessions()__  
Retrieve all sessions attached to the dispatcher.