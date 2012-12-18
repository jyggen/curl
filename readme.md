# Curl [![Build Status](https://secure.travis-ci.org/jyggen/curl.png?branch=master)](https://travis-ci.org/jyggen/curl)

A lightweight cURL library with support for multiple requests in parallel.

[Find Curl on Packagist/Composer](https://packagist.org/packages/jyggen/curl)

## Changelog

*This library is still under development.*

## Usage

This library was created with simplicity in mind, so in most cases you can use the static helpers in the `Curl` class. There's currently two helpers available; `get` and `post`.

```php
$response = Curl::get('http://example.com/');

print_r($response);
```

This will return an array with two indexes: `data` and `info`. `data` contains the response from your request and `info` anything that can be retrieved by `curl_getinfo()`.

If you want to request multiple URLs you can pass an array to the helper, this will utilize parallel (commonly referred to as multi-threaded) requests.

```php
$responses = Curl::get(array('http://example.com/', 'http://example.org/'));

foreach($responses as $response) {
	print_r($response);
}
```
