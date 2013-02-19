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

class SessionTests extends PHPUnit_Framework_TestCase
{

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

		$this->assertSame('http://example.com/', $this->forgeSession()->getInfo(CURLINFO_EFFECTIVE_URL));

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
		$this->assertSame ('http://example.org/', $session->getInfo(CURLINFO_EFFECTIVE_URL));

	}

	/**
     * @expectedException        jyggen\CurlErrorException
     * @expectedExceptionMessage Couldn't set option
     */
	public function testSetOptionError()
	{

		$session = new Session('http://example.com/');
		@$session->setOption(CURLOPT_FILE, 'nope');

	}

	/**
     * @expectedException        jyggen\CurlErrorException
     * @expectedExceptionMessage Couldn't set option
     */
	public function testSetOptionArrayError()
	{

		$session = new Session('http://example.com/');
		@$session->setOption(array(CURLOPT_FOLLOWLOCATION => true, CURLOPT_FILE => 'nope'));

	}

	/**
     * @expectedException        jyggen\ProtectedOptionException
     * @expectedExceptionMessage not allowed to change
     */
	public function testSetProtectedOption()
	{

		$session = new Session('http://example.com/');
		$session->setOption(CURLOPT_RETURNTRANSFER, true);

	}

	public function testAddMultiHandle()
	{

		$session = new Session('http://example.com/');
		$multi   = curl_multi_init();
		$this->assertEquals(0, $session->addMultiHandle($multi));

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
     * @expectedException        jyggen\CurlErrorException
     * @expectedExceptionMessage not resolve host
     */
	public function testExecuteWithError()
	{

		$session = $this->forgeSession('foobar');
		$session->execute();

	}

	public function testIsSuccessful()
	{

		$session = new Session('http://example.com/');
		$this->assertTrue($session->isSuccessful());

	}

	public function testRemoveMultiHandle()
	{

		$session = new Session('http://example.com/');
		$multi   = curl_multi_init();
		$session->addMultiHandle($multi);
		$this->assertEquals(0, $session->removeMultiHandle($multi));

	}

	protected function forgeSession($url = null)
	{

		$url or $url = 'http://example.com/';

		return new Session($url);

	}

}
