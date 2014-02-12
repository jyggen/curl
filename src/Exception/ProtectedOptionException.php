<?php
/**
 * A simple and lightweight cURL library with support for multiple requests in parallel.
 *
 * @package     Curl
 * @version     3.0.1
 * @author      Jonas Stendahl
 * @license     MIT License
 * @copyright   2013 Jonas Stendahl
 * @link        http://github.com/jyggen/curl
 */

namespace Jyggen\Curl\Exception;

/**
 * ProtectedOptionException
 *
 * An exception thrown when trying to change a protected value or option.
 */
class ProtectedOptionException extends \Exception
{
}
