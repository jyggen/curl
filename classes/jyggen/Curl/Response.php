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

namespace jyggen\Curl;

class Response extends \Fleck\Http\Response
{

	public static function forge(array $response)
	{

		return new Response();

	}

}