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
use Mockery as m;

class CurlTests extends PHPUnit_Framework_TestCase
{

	protected $dispatcher;
	protected $session;

	public function setup()
	{

		$this->dispatcher = m::mock('jyggen\\Curl\\DispatcherInterface');
		$this->session    = m::mock('jyggen\\Curl\\SessionInterface');

		Curl::setDispatcher(get_class($this->dispatcher));
		Curl::setSession(get_class($this->session));

	}

	public function teardown()
	{

		m::close();

	}

	public function testSetDispatcherAndGetDispatcher()
	{

		$this->assertEquals(get_class($this->dispatcher), Curl::getDispatcher());

	}

	/**
     * @expectedException        jyggen\UnexpectedValueException
     * @expectedExceptionMessage must implement
     */
	public function testSetDispatcherWithError()
	{

		Curl::setDispatcher('foobar');

	}


	public function testSetSessionAndGetSession()
	{

		$this->assertEquals(get_class($this->session), Curl::getSession());

	}

	/**
     * @expectedException        jyggen\UnexpectedValueException
     * @expectedExceptionMessage must implement
     */
	public function testSetSessionWithError()
	{

		Curl::setSession('foobar');

	}

	public function testDelete()
	{

		//Curl::delete('http://example.com/');

	}

	public function testGet()
	{

		//Curl::get('http://example.com/');

	}

	public function testPost()
	{

		//Curl::post('http://example.com/');

	}

	public function testPut()
	{

		//Curl::put('http://example.com/');

	}

}
