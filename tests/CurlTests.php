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

	public function teardown()
	{

		m::close();

	}

	public function testSetDispatcherAndGetDispatcher()
	{

		$dispatcher = m::mock('jyggen\\Curl\\DispatcherInterface');

		Curl::setDispatcher(get_class($dispatcher));

		$this->assertEquals(get_class($dispatcher), Curl::getDispatcher());

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

		$session = m::mock('jyggen\\Curl\\SessionInterface');

		Curl::setSession(get_class($session));

		$this->assertEquals(get_class($session), Curl::getSession());

	}

	/**
     * @expectedException        jyggen\UnexpectedValueException
     * @expectedExceptionMessage must implement
     */
	public function testSetSessionWithError()
	{

		Curl::setSession('foobar');

	}

}
