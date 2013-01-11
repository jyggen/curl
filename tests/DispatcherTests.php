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
		$this->assertFalse($dispatcher->get(1));

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

		$this->assertEquals(0, $dispatcher->add($session));
		$this->assertInstanceof('jyggen\\Curl\SessionInterface', $dispatcher->get(0));

		$dispatcher->remove(0);

		$this->assertEquals(array(), $dispatcher->get());

	}

	public function testExecute()
	{

		$dispatcher = new Dispatcher;

		$session = m::mock('jyggen\\Curl\\SessionInterface');
		$curl    = curl_init('http://example.com/');

		$session->shouldReceive('addMultiHandle')->with(m::type('resource'))->andReturn(function($handle) use ($curl) {
			return curl_multi_add_handle($handle, $curl);
		});
		$session->shouldReceive('removeMultiHandle')->with(m::type('resource'))->andReturn(function($handle) use ($curl) {
			return curl_multi_remove_handle($handle, $curl);
		});
		$session->shouldReceive('isSuccessful')->andReturn(true);
		$session->shouldReceive('execute');

		$dispatcher->add($session);
		$dispatcher->execute();

	}

	/**
     * @expectedException        jyggen\CurlErrorException
     * @expectedExceptionMessage fake message
     */
	public function testExecuteWithError()
	{

		$dispatcher = new Dispatcher;

		$session = m::mock('jyggen\\Curl\\SessionInterface');
		$curl    = curl_init('invalid://example.com/');

		$session->shouldReceive('addMultiHandle')->with(m::type('resource'))->andReturn(function($handle) use ($curl) {
			return curl_multi_add_handle($handle, $curl);
		});
		$session->shouldReceive('removeMultiHandle')->with(m::type('resource'))->andReturn(function($handle) use ($curl) {
			return curl_multi_remove_handle($handle, $curl);
		});
		$session->shouldReceive('isSuccessful')->andReturn(false);
		$session->shouldReceive('getErrorMessage')->andReturn('fake message');

		$dispatcher->add($session);
		$dispatcher->execute();

	}

}
