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

use jyggen\Curl;
use Mockery as m;

class CurlTests extends PHPUnit_Framework_TestCase
{

    public function teardown()
    {

        m::close();

    }

	/**
     * @expectedException        jyggen\Curl\Exception\BadMethodCallException
     * @expectedExceptionMessage undefined method
     */
	public function testBadMethod()
	{

		Curl::thisDoesNotExist();

	}

	/**
     * @expectedException        jyggen\Curl\Exception\InvalidArgumentException
     * @expectedExceptionMessage Missing argument
     */
	public function testDeleteIsValid()
	{

		Curl::delete();

	}

	/**
     * @expectedException        jyggen\Curl\Exception\InvalidArgumentException
     * @expectedExceptionMessage Missing argument
     */
	public function testGetIsValid()
	{

		Curl::get();

	}

	/**
     * @expectedException        jyggen\Curl\Exception\InvalidArgumentException
     * @expectedExceptionMessage Missing argument
     */
	public function testPostIsValid()
	{

		Curl::post();

	}

	/**
     * @expectedException        jyggen\Curl\Exception\InvalidArgumentException
     * @expectedExceptionMessage Missing argument
     */
	public function testPutIsValid()
	{

		Curl::put();

	}

	public function testDelete()
	{

        $response = Curl::delete('http://httpbin.org/delete');
        $this->assertInstanceOf('jyggen\\Curl\\Response', $response);
        $content = json_decode($response->getContent());
        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('http://httpbin.org/delete', $content->url);

	}

	public function testGet()
	{

        $response = Curl::get('http://httpbin.org/get');
        $this->assertInstanceOf('jyggen\\Curl\\Response', $response);
        $content = json_decode($response->getContent());
        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('http://httpbin.org/get', $content->url);

	}

	public function testPost()
	{

        $response = Curl::post('http://httpbin.org/post');
        $this->assertInstanceOf('jyggen\\Curl\\Response', $response);
        $content = json_decode($response->getContent());
        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('http://httpbin.org/post', $content->url);

	}

	public function testPut()
	{

        $response = Curl::put('http://httpbin.org/put');
        $this->assertInstanceOf('jyggen\\Curl\\Response', $response);
        $content = json_decode($response->getContent());
        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('http://httpbin.org/put', $content->url);

	}

    public function testMultipleUrls()
    {

        $responses = Curl::get(array('http://httpbin.org/get?bar=foo', 'http://httpbin.org/get?foo=bar'));
        $this->assertInstanceOf('jyggen\\Curl\\Response', $responses[0]);
        $content = json_decode($responses[0]->getContent());
        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('foo', $content->args->bar);
        $this->assertInstanceOf('jyggen\\Curl\\Response', $responses[1]);
        $content = json_decode($responses[1]->getContent());
        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('bar', $content->args->foo);

    }

    public function testPostWithData()
    {

        $response = Curl::post('http://httpbin.org/post', array('foo' => 'bar', 'bar' => 'foo'));
        $this->assertInstanceOf('jyggen\\Curl\\Response', $response);
        $content = json_decode($response->getContent());
        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('bar', $content->form->foo);
        $this->assertSame('foo', $content->form->bar);

    }

    public function testPutWithData()
    {

        $response = Curl::put('http://httpbin.org/put', array('foo' => 'bar', 'bar' => 'foo'));
        $this->assertInstanceOf('jyggen\\Curl\\Response', $response);
        $content = json_decode($response->getContent());
        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('foo=bar&bar=foo', $content->data);

    }

    /**
     * @expectedException        jyggen\Curl\Exception\InvalidArgumentException
     * @expectedExceptionMessage implement SessionInterface
     */
    public function testConstructException()
    {

        $dispatcher = new \jyggen\Curl\Dispatcher;
        new Curl('GET', $dispatcher, array('session'), array());

    }

    public function testGithubIssueNoFive()
    {

        /**
         * @todo This is dark magic. Will have to rewrite it properly someday.
         */

        $dispatcher = new \jyggen\Curl\Dispatcher;
        $session    = m::mock('jyggen\\Curl\\Session', array('http://httpbin.org/get'))->makePartial();

        $session->shouldReceive('setOption')->andReturnUsing(function($arg1, $arg2) {
            if ($arg1 === 14 and $arg2 !== 74) {
                throw new Exception('Invalid size of multi-byte string!');
            }
        });

        $curl     = new Curl('PUT', $dispatcher, array($session), array(array('マルチバイト文字')));
        $sessions = $dispatcher->get();

        $this->assertSame($sessions[0]->getResponse()->getStatusCode(), 200);

    }

}
