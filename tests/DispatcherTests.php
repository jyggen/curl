<?php
/**
 * A simple and lightweight cURL library with support for multiple requests in parallel.
 *
 * @package     Curl
 * @version     2.1
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
		$request    = m::mock('jyggen\\Curl\\RequestInterface');

		$request->shouldReceive('addMultiHandle')->andReturn(0);

		$this->assertEquals(0, $dispatcher->add($request));
		$this->assertInstanceof('jyggen\\Curl\RequestInterface', $dispatcher->get(0));

	}

	public function testClear()
	{

		$dispatcher = new Dispatcher;
		$request    = m::mock('jyggen\\Curl\\RequestInterface');

		$request->shouldReceive('addMultiHandle')->andReturn(0);
		$request->shouldReceive('removeMultiHandle')->andReturn(0);

		$this->assertEquals(0, $dispatcher->add($request));
		$this->assertInstanceof('jyggen\\Curl\RequestInterface', $dispatcher->get(0));

		$dispatcher->clear();

		$this->assertEquals(array(), $dispatcher->get());

	}

	public function testRemove()
	{

		$dispatcher = new Dispatcher;
		$request    = m::mock('jyggen\\Curl\\RequestInterface');

		$request->shouldReceive('addMultiHandle')->andReturn(0);
		$request->shouldReceive('removeMultiHandle')->andReturn(0);

		$dispatcher->add($request);
		$dispatcher->remove(0);

		$this->assertEquals(array(), $dispatcher->get());

	}

	public function testExecute()
	{

		$dispatcher = new Dispatcher;
		$request1   = m::mock('jyggen\\Curl\\Request', array('http://example.com/'))->shouldDeferMissing();
		$request2   = m::mock('jyggen\\Curl\\Request', array('http://example.org/'))->shouldDeferMissing();

		$request1->shouldReceive('execute')->andReturn(true);
		$request2->shouldReceive('execute')->andReturn(true);
		$request1->shouldReceive('getRawResponse')->andReturnUsing(function() use ($request1) { return curl_multi_getcontent($request1->getHandle()); });
		$request2->shouldReceive('getRawResponse')->andReturnUsing(function() use ($request2) { return curl_multi_getcontent($request2->getHandle()); });

		$dispatcher->add($request1);
		$dispatcher->add($request2);
		$dispatcher->execute();

		$response1 = $request1->getRawResponse();
		$response2 = $request2->getRawResponse();

		$this->assertStringStartsWith('HTTP/1.1 200 OK', $response1);
		$this->assertStringStartsWith('HTTP/1.1 200 OK', $response2);

	}

	public function testExecuteWithError()
	{

		/**
         * @todo Write test(s) if possible.
         */

	}

}
