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

use jyggen\Curl\Response;
use Mockery as m;

class ResponseTest extends \PHPUnit_Framework_TestCase
{

    public function teardown()
    {

        m::close();

    }

    public function testForge()
    {

        $request   = m::mock('jyggen\\Curl\\RequestInterface');
        $response  = 'HTTP/1.1 503 Service Temporarily Unavailable'."\r\n";
        $response .= 'Server: nginx'."\r\n";
        $response .= 'Date: Tue, 19 Feb 2013 12:59:40 GMT'."\r\n";
        $response .= 'Content-Type: text/html; charset=utf-8'."\r\n";
        $response .= 'Set-Cookie: foo=bar; expires=Mon, 23-Dec-2019 23:50:00 GMT; path=/; domain=localhost'."\r\n";
        $response .= "\r\n";
        $response .= 'supermegafoxyawesomehot';

        $request->shouldReceive('getRawResponse')->andReturn($response);
        $request->shouldReceive('getInfo')->with(CURLINFO_HEADER_SIZE)->andReturn(226);
        $request->shouldReceive('getInfo')->andReturn(array('http_code' => 503));

        $response = Response::forge($request);

        $this->assertSame('supermegafoxyawesomehot', $response->getContent());
        $this->assertSame('1.1', $response->getProtocolVersion());
        $this->assertSame('utf-8', $response->getCharset());
        $this->assertSame('2013-02-19 12:59:40', $response->getDate()->format('Y-m-d H:i:s'));
        $this->assertSame('nginx', $response->headers->get('Server'));
        $this->assertSame(503, $response->getStatusCode());
        $this->assertSame(true, $response->isServerError());

    }

}
