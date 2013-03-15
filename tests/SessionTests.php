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

use jyggen\Curl\Session;
use Mockery as m;

class SessionTests extends PHPUnit_Framework_TestCase
{

    public function teardown()
    {

        m::close();

    }

    public function testConstruct()
    {

        $this->assertInstanceof('jyggen\\Curl\\SessionInterface', $this->forgeSession());

    }

    public function testGetErrorMessage()
    {

        $this->assertSame(null, $this->forgeSession()->getErrorMessage());

    }

    public function testGetHandle()
    {

        $session = $this->forgeSession();
        $this->assertInternalType('resource', $session->getHandle());
        $this->assertSame('curl', get_resource_type($session->getHandle()));

    }

    public function testGetInfo()
    {

        $this->assertInternalType('array', $this->forgeSession()->getInfo());

    }

    public function testGetInfoWithKey()
    {

        $this->assertSame('http://httpbin.org/get', $this->forgeSession()->getInfo(CURLINFO_EFFECTIVE_URL));

    }

    public function testGetResponse()
    {

        $this->assertSame(null, $this->forgeSession()->getResponse());

    }

    public function testSetOption()
    {

        $session = $this->forgeSession();
        $session->setOption(CURLOPT_URL, 'http://example.org/');
        $this->assertSame('http://example.org/', $session->getInfo(CURLINFO_EFFECTIVE_URL));

    }

    public function testSetOptionArray()
    {

        $session = $this->forgeSession();
        $session->setOption(array(CURLOPT_FOLLOWLOCATION => true, CURLOPT_URL => 'http://example.org/'));
        $this->assertSame('http://example.org/', $session->getInfo(CURLINFO_EFFECTIVE_URL));

    }

    /**
     * @expectedException        jyggen\Curl\Exception\CurlErrorException
     * @expectedExceptionMessage Couldn't set option
     */
    public function testSetOptionError()
    {

        $session = $this->forgeSession();
        @$session->setOption(CURLOPT_FILE, 'nope');

    }

    /**
     * @expectedException        jyggen\Curl\Exception\CurlErrorException
     * @expectedExceptionMessage Couldn't set option
     */
    public function testSetOptionArrayError()
    {

        $session = $this->forgeSession();
        @$session->setOption(array(CURLOPT_FOLLOWLOCATION => true, CURLOPT_FILE => 'nope'));

    }

    /**
     * @expectedException        jyggen\Curl\Exception\ProtectedOptionException
     * @expectedExceptionMessage protected option
     */
    public function testSetProtectedOption()
    {

        $session = $this->forgeSession();
        $session->setOption(CURLOPT_RETURNTRANSFER, true);

    }

    public function testAddMultiHandle()
    {

        $session = $this->forgeSession();
        $multi   = curl_multi_init();
        $this->assertTrue($session->addMultiHandle($multi));

    }

    /**
     * @expectedException        jyggen\Curl\Exception\CurlErrorException
     * @expectedExceptionMessage curl_multi
     */
    public function testAddMultiHandleWithInvalidHandle()
    {

        $session = new Session('http://example.com/');
        $this->assertTrue($session->addMultiHandle('lolnope'));

    }

    /**
     * @expectedException        jyggen\Curl\Exception\CurlErrorException
     * @expectedExceptionMessage code
     */
    public function testAddMultiHandleWithErrorCode()
    {

        /**
         * @todo Write proper test(s):
         */

        // $resource = tmpfile();
        // $session  = m::mock('jyggen\\Curl\\Session[isValidMultiHandle]');
        // $session->shouldReceive('isValidMultiHandle')->andReturn(true);
        // $return = @$session->addMultiHandle($resource);

    }

    public function testIsExecuted()
    {

        $this->assertFalse($this->forgeSession()->isExecuted());

    }

    public function testExecute()
    {

        $session = $this->forgeSession();
        $session->execute();
        $this->assertTrue($session->isExecuted());

    }

    /**
     * @expectedException        jyggen\Curl\Exception\CurlErrorException
     * @expectedExceptionMessage resolve host
     */
    public function testExecuteWithError()
    {

        $session = $this->forgeSession('foobar');
        $session->execute();

    }

    public function testIsSuccessful()
    {

        $session = $this->forgeSession();
        $this->assertTrue($session->isSuccessful());

    }

    public function testRemoveMultiHandle()
    {

        $session = $this->forgeSession();
        $multi   = curl_multi_init();
        $session->addMultiHandle($multi);
        $this->assertSame(0, $session->removeMultiHandle($multi));

    }

    public function testRawResponse()
    {

        $session = $this->forgeSession();
        $this->assertSame(null, $session->getRawResponse());

    }

    protected function forgeSession($url = null)
    {

        $url or $url = 'http://httpbin.org/get';

        return new Session($url);

    }

}
