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

use jyggen\Curl\Dispatcher;
use jyggen\Curl\Session;

class DispatcherTests extends PHPUnit_Framework_TestCase
{

	protected static $instance;

	public static function getInstance()
	{

		if (static::$instance == null) {

			static::$instance = new Dispatcher;

		}

		return static::$instance;

	}

	public function testConstruct()
	{

		$dispatcher = static::getInstance();

		$this->assertInstanceOf('jyggen\\Curl\\Dispatcher', $dispatcher);

	}

	public function testGetSessions()
	{

		$sessions = static::getInstance()->getSessions();

		$this->assertTrue(is_array($sessions));
		$this->assertTrue(empty($sessions));

	}

	public function testAddSessionSingle()
	{

		$dispatcher = static::getInstance();
		$session    = new Session('http://example.com/');

		$dispatcher->addSession($session);

		$sessions = $dispatcher->getSessions();
		$this->assertEquals('http://example.com/', $sessions[0]->getInfo(CURLINFO_EFFECTIVE_URL));

	}

	public function testAddSessionMultiple()
	{

		$dispatcher = static::getInstance();

		$dispatcher->addSession(array(new Session('http://example.net/'), new Session('http://example.org/')));

		$sessions = $dispatcher->getSessions();

		$this->assertEquals('http://example.net/', $sessions[1]->getInfo(CURLINFO_EFFECTIVE_URL));
		$this->assertEquals('http://example.org/', $sessions[2]->getInfo(CURLINFO_EFFECTIVE_URL));

	}

	public function testAddSessionException()
	{

		$this->setExpectedException('jyggen\\UnexpectedValueException');
		static::getInstance()->addSession('session');

	}

}
