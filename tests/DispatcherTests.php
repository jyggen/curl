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

use jyggen\Curl\Dispatcher;
use Mockery as m;

class DispatcherTests extends PHPUnit_Framework_TestCase
{

	public function teardown()
	{

		m::close();

	}

	public function testConstruct()
	{

		$this->assertInstanceof('jyggen\\Curl\\Dispatcher', new Dispatcher);

	}

	public function testGet()
	{

		$dispatcher = new Dispatcher;
		$this->assertEquals(array(), $dispatcher->get());
		$this->assertNull($dispatcher->get(1));

	}

	public function testAdd()
	{

		$dispatcher = new Dispatcher;
		$session    = m::mock('jyggen\\Curl\\SessionInterface');

		$session->shouldReceive('addMultiHandle')->andReturn(0);

		$this->assertEquals(0, $dispatcher->add($session));
		$this->assertInstanceof('jyggen\\Curl\SessionInterface', $dispatcher->get(0));

	}

	public function testClear()
	{

		$dispatcher = new Dispatcher;
		$session    = m::mock('jyggen\\Curl\\SessionInterface');

		$session->shouldReceive('addMultiHandle')->andReturn(0);
		$session->shouldReceive('removeMultiHandle')->andReturn(0);

		$this->assertEquals(0, $dispatcher->add($session));
		$this->assertInstanceof('jyggen\\Curl\SessionInterface', $dispatcher->get(0));

		$dispatcher->clear();

		$this->assertEquals(array(), $dispatcher->get());

	}

	public function testRemove()
	{

		$dispatcher = new Dispatcher;
		$session    = m::mock('jyggen\\Curl\\SessionInterface');

		$session->shouldReceive('addMultiHandle')->andReturn(0);
		$session->shouldReceive('removeMultiHandle')->andReturn(0);

		$dispatcher->add($session);
		$dispatcher->remove(0);

		$this->assertEquals(array(), $dispatcher->get());

	}

	public function testExecute()
	{

		$dispatcher = new Dispatcher;
		$session1   = m::mock('jyggen\\Curl\\Session', array('http://example.com/'))->shouldDeferMissing();
		$session2   = m::mock('jyggen\\Curl\\Session', array('http://example.org/'))->shouldDeferMissing();

		$session1->shouldReceive('execute')->andReturn(true);
		$session2->shouldReceive('execute')->andReturn(true);
		$session1->shouldReceive('getRawResponse')->andReturnUsing(function() use ($session1) { return curl_multi_getcontent($session1->getHandle()); });
		$session2->shouldReceive('getRawResponse')->andReturnUsing(function() use ($session2) { return curl_multi_getcontent($session2->getHandle()); });

		$dispatcher->add($session1);
		$dispatcher->add($session2);
		$dispatcher->execute();

		$response1 = $session1->getRawResponse();
		$response2 = $session2->getRawResponse();

		$this->assertStringStartsWith('HTTP/1.0 302 Found', $response1);
		$this->assertStringStartsWith('HTTP/1.0 302 Found', $response2);

	}

	public function testExecuteWithError()
	{

		/**
         * @todo Write proper test(s):
         */

		// $dispatcher = m::mock('jyggen\\Curl\\Dispatcher')->shouldDeferMissing();
		// $dispatcher->shouldReceive('process')->andReturn(array(1, false));
		// $dispatcher->execute();

	}

}
