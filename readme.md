# Curl [![Build Status](https://secure.travis-ci.org/jyggen/curl.png?branch=master)](https://travis-ci.org/jyggen/curl)

A lightweight cURL library with support for multiple requests in parallel.

[Find Curl on Packagist/Composer](https://packagist.org/packages/jyggen/curl)

## Changelog

*This library is still under development.*

## Usage

### Static Helpers

This library was created with simplicity in mind, so in most cases you can use the static helpers in the `Curl` class. Every helper will return an array with two indexes: `data` and `info`. `data` will contain the response from your request and `info` anything that can normally be retrieved by `curl_getinfo()`. If multiple URLs are requested the helpers will return an array with a response array for each URL.

#### GET

The first helper is `Curl::get()` which simply makes a GET request to the URL you supply.

```php
$response = jyggen\Curl::get('http://example.com/');
```

If you want to request multiple URLs you can pass an array to the helper. This will utilize parallel requests (commonly referred to as multi-threaded).

```php
$responses = jyggen\Curl::get(array('http://example.com/', 'http://example.org/'));
```

#### POST

The next helper is `Curl::post()`. This method requires two arguments; the request URL and an array of POST data.

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

### Session and Dispatcher

For more advanced usage you'll have to go for the classes underneath the helpers. First we have the `Session`, which could be referred to as your URL, and then there's `Dispatcher` which keeps track of your sessions and executes your requests.
