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

use jyggen\Curl\Response;
use Mockery as m;

class ResponseTests extends PHPUnit_Framework_TestCase
{

    public function teardown()
    {

        m::close();

    }

    public function testForge()
    {

        $session   = m::mock('jyggen\\Curl\\SessionInterface');
        $response  = 'HTTP/1.1 503 Service Temporarily Unavailable'."\r\n";
        $response .= 'Server: nginx'."\r\n";
        $response .= 'Date: Tue, 19 Feb 2013 12:59:40 GMT'."\r\n";
        $response .= 'Content-Type: text/html; charset=utf-8'."\r\n";
        $response .= 'Set-Cookie: foo=bar; expires=Mon, 23-Dec-2019 23:50:00 GMT; path=/; domain=localhost'."\r\n";
        $response .= "\r\n";
        $response .= 'supermegafoxyawesomehot';

        $session->shouldReceive('getRawResponse')->andReturn($response);
        $session->shouldReceive('getInfo')->with(CURLINFO_HEADER_SIZE)->andReturn(226);
        $session->shouldReceive('getInfo')->andReturn(array('http_code' => 503));

        $response = Response::forge($session);

        $this->assertSame('supermegafoxyawesomehot', $response->getContent());
        $this->assertSame('1.1', $response->getProtocolVersion());
        $this->assertSame('utf-8', $response->getCharset());
        $this->assertSame('2013-02-19 12:59:40', $response->getDate()->format('Y-m-d H:i:s'));
        $this->assertSame('nginx', $response->headers->get('Server'));
        $this->assertSame(503, $response->getStatusCode());
        $this->assertSame(true, $response->isServerError());

    }

}