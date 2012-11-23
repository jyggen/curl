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

use jyggen\Curl;

class CurlTests extends PHPUnit_Framework_TestCase
{

	public function testForge()
	{

		$this->assertInstanceof('jyggen\\Curl\\Request', Curl::forge('curl-1'));
		$this->assertInstanceof('jyggen\\Curl\\Request', Curl::forge());

		$this->setExpectedException('Exception');
		Curl::forge('curl-1');

	}

	public function testInstance()
	{

		$this->assertInstanceof('jyggen\\Curl\\Request', Curl::instance('curl-1'));
		$this->assertInstanceof('jyggen\\Curl\\Request', Curl::instance());

		$this->assertSame(Curl::instance('curl-1'), Curl::instance('curl-1'));
		$this->assertNotSame(Curl::instance('curl-1'), Curl::instance());

		$this->assertFalse(Curl::instance('curl-2'));

	}

	public function testGet()
	{

		Curl::get('http://www.example.com/');
		Curl::get(array('http://www.example.com/', 'http://www.example.org/'));

	}

	public function testPost()
	{

		#Curl::post();

	}

}