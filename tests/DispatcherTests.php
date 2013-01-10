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

	public function testExecuteEmpty()
	{

		$this->setExpectedException('jyggen\\EmptyDispatcherException');
		static::getInstance()->execute();

	}

	public function testAddSessionSingle()
	{

		$dispatcher = static::getInstance();
		$session    = new Session('http://example.com/');

		$session->setOption(CURLOPT_FOLLOWLOCATION, true);
		$dispatcher->addSession($session);

		$sessions = $dispatcher->getSessions();
		$this->assertEquals('http://example.com/', $sessions[0]->getInfo(CURLINFO_EFFECTIVE_URL));

	}

	public function testExecuteSingle()
	{

		$dispatcher = static::getInstance();
		$sessions   = $dispatcher->getSessions();

		$dispatcher->execute();

		$response = $sessions[0]->getResponse();

		$this->assertTrue(array_key_exists('data', $response));
		$this->assertTrue(array_key_exists('info', $response));
		$this->assertEquals(200, $response['info']['http_code']);
		$this->assertEquals('http://www.iana.org/domains/example', $response['info']['url']);
		$this->assertSelectEquals('html body div h1', 'Example Domain', true, $response['data']);

	}

	public function testAddSessionMultiple()
	{

		$dispatcher = static::getInstance();

		$session1 = new Session('http://example.net/');
		$session1->setOption(CURLOPT_FOLLOWLOCATION, true);

		$session2 = new Session('http://example.org/');
		$session2->setOption(CURLOPT_FOLLOWLOCATION, true);

		$dispatcher->addSession(array($session1, $session2));

		$sessions = $dispatcher->getSessions();

		$this->assertEquals('http://example.net/', $sessions[1]->getInfo(CURLINFO_EFFECTIVE_URL));
		$this->assertEquals('http://example.org/', $sessions[2]->getInfo(CURLINFO_EFFECTIVE_URL));

	}

	public function testExecuteWithKey()
	{

		$dispatcher = static::getInstance();
		$sessions   = $dispatcher->getSessions();

		$dispatcher->execute(1);

		$response = $sessions[1]->getResponse();

		$this->assertTrue(array_key_exists('data', $response));
		$this->assertTrue(array_key_exists('info', $response));
		$this->assertEquals(200, $response['info']['http_code']);
		$this->assertEquals('http://www.iana.org/domains/example', $response['info']['url']);
		$this->assertSelectEquals('html body div h1', 'Example Domain', true, $response['data']);

	}

	public function testExecuteMultiple()
	{

		$dispatcher = static::getInstance();
		$sessions   = $dispatcher->getSessions();

		$dispatcher->execute();

		$response = $sessions[2]->getResponse();

		$this->assertTrue(array_key_exists('data', $response));
		$this->assertTrue(array_key_exists('info', $response));
		$this->assertEquals(200, $response['info']['http_code']);
		$this->assertEquals('http://www.iana.org/domains/example', $response['info']['url']);
		$this->assertSelectEquals('html body div h1', 'Example Domain', true, $response['data']);

	}

	public function testGetResponses()
	{

		$responses = static::getInstance()->getResponses();

		$this->assertTrue(is_array($responses));
		$this->assertEquals(3, count($responses));

		foreach ($responses as $response) {

			$this->assertTrue(array_key_exists('data', $response));
			$this->assertTrue(array_key_exists('info', $response));
			$this->assertEquals(200, $response['info']['http_code']);
			$this->assertEquals('http://www.iana.org/domains/example', $response['info']['url']);
			$this->assertSelectEquals('html body div h1', 'Example Domain', true, $response['data']);

		}

	}

	public function testAddSessionException()
	{

		$this->setExpectedException('jyggen\\UnexpectedValueException');
		static::getInstance()->addSession('session');

	}

	public function testRemoveSession()
	{

		$dispatcher  = static::getInstance();
		$resultTrue  = $dispatcher->removeSession(2);
		$resultFalse = $dispatcher->removeSession(2);
		$sessions    = $dispatcher->getSessions();

		$this->assertTrue($resultTrue);
		$this->assertFalse($resultFalse);
		$this->assertEquals(2, count($sessions));

	}

	public function testClearSessions()
	{

		$sessions = static::getInstance()->clearSessions()->getSessions();

		$this->assertTrue(empty($sessions));

	}

}
