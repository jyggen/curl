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

		$this->assertInstanceof('jyggen\\Curl\\SessionInterface', new Session('http://example.com/'));

	}

	public function testGetErrorMessage()
	{

		$session = new Session('http://example.com/');
		$this->assertEquals('', $session->getErrorMessage());

	}

	public function testGetHandle()
	{

		$session = new Session('http://example.com/');
		$this->assertInternalType('resource', $session->getHandle());
		$this->assertEquals('curl', get_resource_type($session->getHandle()));

	}

	public function testGetInfo()
	{

		$session = new Session('http://example.com/');
		$this->assertInternalType('array', $session->getInfo());

	}

	public function testGetInfoWithKey()
	{

		$session = new Session('http://example.com/');
		$this->assertEquals('http://example.com/', $session->getInfo(CURLINFO_EFFECTIVE_URL));

	}

	public function testGetResponse()
	{

		$session = new Session('http://example.com/');
		$this->assertEquals(null, $session->getResponse());

	}

	public function testSetOption()
	{

		$session = new Session('http://example.com/');
		$session->setOption(CURLOPT_URL, 'http://example.org/');
		$this->assertEquals('http://example.org/', $session->getInfo(CURLINFO_EFFECTIVE_URL));

	}

	public function testSetOptionArray()
	{

		$session = new Session('http://example.com/');
		$session->setOption(array(CURLOPT_FOLLOWLOCATION => true, CURLOPT_URL => 'http://example.org/'));
		$this->assertEquals('http://example.org/', $session->getInfo(CURLINFO_EFFECTIVE_URL));

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

	public function testExecute()
	{

		$session = new Session('http://example.com/');

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

}
