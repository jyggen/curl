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

use jyggen\Curl;
use Mockery as m;

class CurlTests extends PHPUnit_Framework_TestCase
{

    public function teardown()
    {
        m::close();
    }

    public function testDelete()
    {
        $responses = Curl::delete('http://httpbin.org/delete');
        $this->assertInstanceOf('jyggen\\Curl\\Response', $responses[0]);
        $content = json_decode($responses[0]->getContent());
        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('http://httpbin.org/delete', $content->url);
    }

    public function testGet()
    {
        $responses = Curl::get('http://httpbin.org/get');
        $this->assertInstanceOf('jyggen\\Curl\\Response', $responses[0]);
        $content = json_decode($responses[0]->getContent());
        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('http://httpbin.org/get', $content->url);
    }

    public function testPost()
    {
        $responses = Curl::post('http://httpbin.org/post');
        $this->assertInstanceOf('jyggen\\Curl\\Response', $responses[0]);
        $content = json_decode($responses[0]->getContent());
        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('http://httpbin.org/post', $content->url);
    }

    public function testPut()
    {
        $responses = Curl::put('http://httpbin.org/put');
        $this->assertInstanceOf('jyggen\\Curl\\Response', $responses[0]);
        $content = json_decode($responses[0]->getContent());
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
        $responses = Curl::post('http://httpbin.org/post', array('foo' => 'bar', 'bar' => 'foo'));
        $this->assertInstanceOf('jyggen\\Curl\\Response', $responses[0]);
        $content = json_decode($responses[0]->getContent());
        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('bar', $content->form->foo);
        $this->assertSame('foo', $content->form->bar);
    }

    public function testPutWithData()
    {
        $responses = Curl::put('http://httpbin.org/put', array('foo' => 'bar', 'bar' => 'foo'));
        $this->assertInstanceOf('jyggen\\Curl\\Response', $responses[0]);
        $content = json_decode($responses[0]->getContent());
        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('foo=bar&bar=foo', $content->data);
    }

    /**
     * @expectedException        jyggen\Curl\Exception\InvalidArgumentException
     * @expectedExceptionMessage implement RequestInterface
     */
    public function testConstructException()
    {
        $dispatcher = new \jyggen\Curl\Dispatcher;
        new Curl('GET', $dispatcher, array('request'), array());
    }
}
