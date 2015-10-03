<?php
/**
 * A simple and lightweight cURL library with support for multiple requests in parallel.
 *
 * @package     Curl
 * @version     3.0.1
 * @author      Jonas Stendahl
 * @license     MIT License
 * @copyright   2013 Jonas Stendahl
 * @link        http://github.com/jyggen/curl
 */

namespace Jyggen\Curl;

use Jyggen\Curl\Dispatcher;
use Jyggen\Curl\Request;
use Mockery as m;

class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function teardown()
    {
        m::close();
        global $mockAddMulti;
        $mockAddMulti = false;
    }

    public function testConstruct()
    {
        $this->assertInstanceof('Jyggen\\Curl\\Dispatcher', new Dispatcher);
    }

    public function testGet()
    {
        $dispatcher = new Dispatcher;
        $this->assertSame(array(), $dispatcher->all());
        $this->assertNull($dispatcher->get(1));
        $this->assertSame(array(), $dispatcher->get());
    }

    public function testAdd()
    {
        $dispatcher = new Dispatcher;
        $request    = m::mock('Jyggen\\Curl\\RequestInterface');

        $request->shouldReceive('addMultiHandle')->andReturn(0);

        $this->assertEquals(0, $dispatcher->add($request));
        $this->assertInstanceof('Jyggen\\Curl\RequestInterface', $dispatcher->get(0));
    }

    public function testClear()
    {
        $dispatcher = new Dispatcher;
        $request    = m::mock('Jyggen\\Curl\\RequestInterface');

        $request->shouldReceive('addMultiHandle')->andReturn(0);
        $request->shouldReceive('removeMultiHandle')->andReturn(0);

        $this->assertEquals(0, $dispatcher->add($request));
        $this->assertInstanceof('Jyggen\\Curl\RequestInterface', $dispatcher->get(0));

        $dispatcher->clear();

        $this->assertEquals(array(), $dispatcher->all());
    }

    public function testRemove()
    {
        $dispatcher = new Dispatcher;
        $request    = m::mock('Jyggen\\Curl\\RequestInterface');

        $request->shouldReceive('addMultiHandle')->andReturn(0);
        $request->shouldReceive('removeMultiHandle')->andReturn(0);

        $dispatcher->add($request);
        $dispatcher->remove(0);

        $this->assertEquals(array(), $dispatcher->all());
    }

    public function testExecute()
    {
        $dispatcher = new Dispatcher;
        $request1   = new Request('http://example.com/');
        $request2   = new Request('http://example.org/');

        $dispatcher->add($request1);
        $dispatcher->add($request2);
        $dispatcher->execute();

        $response1 = $request1->getRawResponse();
        $response2 = $request2->getRawResponse();

        $this->assertStringStartsWith('HTTP/1.1 200 OK', $response1);
        $this->assertStringStartsWith('HTTP/1.1 200 OK', $response2);
    }

    /**
     * @expectedException        Jyggen\Curl\Exception\CurlErrorException
     * @expectedExceptionMessage Unable to add request to cURL multi handle (code #4)
     */
    public function testExecuteWithError()
    {
        global $mockAddMulti;

        $mockAddMulti = true;
        $dispatcher   = new Dispatcher;
        $request1     = m::mock('Jyggen\\Curl\\Request', array('http://example.com/'))->shouldDeferMissing();
        $request2     = m::mock('Jyggen\\Curl\\Request', array('http://example.org/'))->shouldDeferMissing();

        $dispatcher->add($request1);
        $dispatcher->add($request2);
        $dispatcher->execute();
    }

    public function testStackSize()
    {
        $dispatcher = new Dispatcher;
        $dispatcher->setStackSize(25);
        $this->assertSame(25, $dispatcher->getStackSize());
    }

    /**
     * @expectedException        Jyggen\Curl\Exception\InvalidArgumentException
     * @expectedExceptionMessage setStackSize() expected an integer
     */
    public function testSetStackSizeInvalidArgument()
    {
        $dispatcher = new Dispatcher;
        $dispatcher->setStackSize('string');
    }

    public function testExecuteStacked()
    {
        $dispatcher = new Dispatcher;

        for ($i = 0; $i <= $dispatcher->getStackSize() + 5; $i++) {
            $request = new Request('http://httpbin.org/get');
            $dispatcher->add($request);
        }

        $dispatcher->execute();
        $requests = $dispatcher->all();

        foreach ($requests as $request) {
            $this->assertStringStartsWith('HTTP/1.1 200 OK', $request->getRawResponse());
        }
    }

    /**
     * @expectedException        Jyggen\Curl\Exception\CurlErrorException
     * @expectedExceptionMessage cURL read error #4
     */
    public function testExecuteWithProcessError()
    {
        global $mockMultiExec;

        $mockMultiExec = true;
        $dispatcher    = new Dispatcher;
        $request1      = m::mock('Jyggen\\Curl\\Request', array('http://example.com/'))->shouldDeferMissing();
        $request2      = m::mock('Jyggen\\Curl\\Request', array('http://example.org/'))->shouldDeferMissing();

        $dispatcher->add($request1);
        $dispatcher->add($request2);
        $dispatcher->execute();
    }

    public function testExecuteWithCallback()
    {
        $dispatcher = new Dispatcher;
        $request    = new Request('http://example.com/');
        $check      = false;

        $dispatcher->add($request);
        $dispatcher->execute(function () use (&$check) {
            $check = true;
        });
        $this->assertTrue($check);
    }
}
