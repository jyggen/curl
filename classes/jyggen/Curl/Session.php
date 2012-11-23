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

class Session
{

	public $handle, $url;

	public function __construct($url)
	{

		$this->url    = $url;
		$this->handle = curl_init($this->url);

	}

	public function execute()
	{

		return curl_exec($this->handle);

	}

	public function getInfo($opt = false)
	{

		if ($opt) {

			return curl_getinfo($this->handle, $opt);

		} else {

			return curl_getinfo($this->handle);

		}

	}

	public function setOption($option, $value = null)
	{

		if (is_array($option)) {

			return curl_setopt_array($this->handle, $option);

		} else {

			return curl_setopt($this->handle, $option, $value);

		}

	}

}