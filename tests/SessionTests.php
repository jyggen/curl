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

	protected static $instance;

	public static function getInstance()
	{

		if(static::$instance == null) {

			static::$instance = new Session('http://example.com/');

		}

		return static::$instance;

	}

	public function testConstruct()
	{

		$this->assertInstanceOf('jyggen\\Curl\\Session', $this->getInstance());

	}

	public function testGetHandle()
	{

		$handle = static::getInstance()->getHandle();

		$this->assertInternalType('resource', $handle);
		$this->assertEquals('curl', get_resource_type($handle));

	}

	public function testGetInfo()
	{

		$info = static::getInstance()->getInfo();

		$this->assertEquals('http://example.com/', $info['url']);

	}

	public function testGetInfoWithKey()
	{

		$this->assertEquals('http://example.com/', static::getInstance()->getInfo(CURLINFO_EFFECTIVE_URL));

	}

	public function testGetResponseBeforeExecute()
	{

		$this->assertNull(static::getInstance()->getResponse());

	}

	public function testSetOption()
	{

		$session = static::getInstance();

		$this->assertTrue($session->setOption(CURLOPT_URL, 'http://example.org/'));
		$this->assertEquals('http://example.org/', $session->getInfo(CURLINFO_EFFECTIVE_URL));

	}

	public function testSetOptionArray()
	{

		$session = static::getInstance();

		$this->assertTrue($session->setOption(array(CURLOPT_FOLLOWLOCATION => true, CURLOPT_URL => 'http://example.com/')));
		$this->assertEquals('http://example.com/', $session->getInfo(CURLINFO_EFFECTIVE_URL));

	}

	public function testSetOptionWithError()
	{

		$this->assertFalse(@static::getInstance()->setOption(CURLOPT_FILE, 'nope'));

	}

	public function testSetOptionArrayWithError()
	{

		$this->assertFalse(@static::getInstance()->setOption(array(CURLOPT_FOLLOWLOCATION => false, CURLOPT_FILE => 'nope')));

	}

	public function testSetProtectedOption()
	{

		$this->setExpectedException('jyggen\\ProtectedOptionException');
		static::getInstance()->setOption(CURLOPT_RETURNTRANSFER, true);

	}

	public function testSetResponseWithBool()
	{

		$session = static::getInstance();

		$session->setResponse(true);

		$response = $session->getResponse();

		$this->assertFalse(array_key_exists('data', $response));
		$this->assertTrue(array_key_exists('info', $response));
		$this->assertEquals(0, $response['info']['http_code']);
		$this->assertEquals('http://example.com/', $response['info']['url']);

	}

	public function testSetResponseWithContent()
	{

		$session = static::getInstance();
		$data    = curl_exec($session->getHandle());

		$session->setResponse($data);

		$response = $session->getResponse();

		$this->assertTrue(array_key_exists('data', $response));
		$this->assertTrue(array_key_exists('info', $response));
		$this->assertEquals(302, $response['info']['http_code']);
		$this->assertEquals('http://example.com/', $response['info']['url']);
		$this->assertSelectEquals('html body div h1', 'Example Domain', true, $response['data']);

	}

}
