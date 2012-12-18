<?php
/**
 * A lightweight cURL library with support for multiple requests in parallel.
 *
 * @package Curl
 * @version 1.0
 * @author Jonas Stendahl
 * @license MIT License
 * @copyright 2012 Jonas Stendahl
 * @link http://github.com/jyggen/curl
 */

namespace jyggen;

class CurlErrorException extends \Exception { }

class EmptyDispatcherException extends \Exception { }

class InvalidKeyException extends \Exception { }

class ProtectedOptionException extends \Exception { }

class UnexpectedValueException extends \Exception { }
