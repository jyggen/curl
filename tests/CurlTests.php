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

class CurlTests extends PHPUnit_Framework_TestCase
{

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

}
