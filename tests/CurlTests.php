<?php
/**
 * A simple and lightweight cURL library with support for multiple requests in parallel.
 *
 * @package     Curl
 * @version     2.0
 * @author      Jonas Stendahl
 * @license     MIT License
 * @copyright   2013 Jonas Stendahl
 * @link        http://github.com/jyggen/curl
 */

use jyggen\Curl;

class CurlTests extends PHPUnit_Framework_TestCase
{

	public function testGet()
	{

		Curl::get('http://example.com/');

	}

}
