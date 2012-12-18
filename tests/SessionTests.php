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

use jyggen\Curl\Session;

class SessionTests extends PHPUnit_Framework_TestCase
{

	protected $instance;

	public function getInstance()
	{

		if($this->instance == null) {

			$this->instance = new Session('http://example.com/');

		}

		return $this->instance;

	}

	public function testConstruct()
	{

		$this->assertInstanceOf('jyggen\\Curl\\Session', $this->getInstance());

	}

	public function testGetHandle()
	{

		$session = $this->getInstance();
		$this->assertInternalType('resource', $session->getHandle());
		$this->assertEquals('curl', get_resource_type($session->getHandle()));

	}

}
