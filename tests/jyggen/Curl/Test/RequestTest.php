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

namespace jyggen\Curl\Test;

use jyggen\Curl\Request;
use Mockery as m;

class RequestTest extends \PHPUnit_Framework_TestCase
{

    public function teardown()
    {

        m::close();

    }

    public function testConstruct()
    {

        $this->assertInstanceof('jyggen\\Curl\\RequestInterface', $this->forgeRequest());

    }

    public function testGetErrorMessage()
    {

        $this->assertSame(null, $this->forgeRequest()->getErrorMessage());

    }

    public function testGetHandle()
    {

        $request = $this->forgeRequest();
        $this->assertInternalType('resource', $request->getHandle());
        $this->assertSame('curl', get_resource_type($request->getHandle()));

    }

    public function testGetInfo()
    {

        $this->assertInternalType('array', $this->forgeRequest()->getInfo());

    }

    public function testGetInfoWithKey()
    {

        $this->assertSame('http://httpbin.org/get', $this->forgeRequest()->getInfo(CURLINFO_EFFECTIVE_URL));

    }

    public function testGetResponse()
    {

        $this->assertSame(null, $this->forgeRequest()->getResponse());

    }

    public function testSetOption()
    {

        $request = $this->forgeRequest();
        $request->setOption(CURLOPT_URL, 'http://example.org/');
        $this->assertSame('http://example.org/', $request->getInfo(CURLINFO_EFFECTIVE_URL));

    }

    public function testSetOptionArray()
    {

        $request = $this->forgeRequest();
        $request->setOption(array(CURLOPT_FOLLOWLOCATION => true, CURLOPT_URL => 'http://example.org/'));
        $this->assertSame('http://example.org/', $request->getInfo(CURLINFO_EFFECTIVE_URL));

    }

    /**
     * @expectedException        jyggen\Curl\Exception\CurlErrorException
     * @expectedExceptionMessage Couldn't set option
     */
    public function testSetOptionError()
    {

        $request = $this->forgeRequest();
        @$request->setOption(CURLOPT_FILE, 'nope');

    }

    /**
     * @expectedException        jyggen\Curl\Exception\CurlErrorException
     * @expectedExceptionMessage Couldn't set option
     */
    public function testSetOptionArrayError()
    {

        $request = $this->forgeRequest();
        @$request->setOption(array(CURLOPT_FOLLOWLOCATION => true, CURLOPT_FILE => 'nope'));

    }

    /**
     * @expectedException        jyggen\Curl\Exception\ProtectedOptionException
     * @expectedExceptionMessage protected option
     */
    public function testSetProtectedOption()
    {

        $request = $this->forgeRequest();
        $request->setOption(CURLOPT_RETURNTRANSFER, true);

    }

    public function testIsExecuted()
    {

        $this->assertFalse($this->forgeRequest()->isExecuted());

    }

    public function testExecute()
    {

        $request = $this->forgeRequest();
        $request->execute();
        $this->assertTrue($request->isExecuted());

    }

    /**
     * @expectedException        jyggen\Curl\Exception\CurlErrorException
     * @expectedExceptionMessage resolve host
     */
    public function testExecuteWithError()
    {

        $request = $this->forgeRequest('foobar');
        $request->execute();

    }

    public function testIsSuccessful()
    {

        $request = $this->forgeRequest();
        $this->assertTrue($request->isSuccessful());

    }

    public function testRawResponse()
    {

        $request = $this->forgeRequest();
        $this->assertSame(null, $request->getRawResponse());

    }

    protected function forgeRequest($url = null)
    {
        $url or $url = 'http://httpbin.org/get';
        return new Request($url);
    }

}
