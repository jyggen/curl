<?php
/**
 * This file is part of the jyggen/curl library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Jonas Stendahl <jonas.stendahl@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @link https://jyggen.com/projects/jyggen-curl Documentation
 * @link https://packagist.org/packages/jyggen/curl Packagist
 * @link https://github.com/jyggen/curl GitHub
 */


namespace Jyggen\Curl;

use Jyggen\Curl\Response;
use Mockery as m;

class ResponseTest extends \PHPUnit_Framework_TestCase
{

    public function teardown()
    {

        m::close();

    }

    public function testForge()
    {

        $request   = m::mock('Jyggen\\Curl\\RequestInterface');
        $response  = 'HTTP/1.1 503 Service Temporarily Unavailable'."\r\n";
        $response .= 'Server: nginx'."\r\n";
        $response .= 'Date: Tue, 19 Feb 2013 12:59:40 GMT'."\r\n";
        $response .= 'Content-Type: text/html; charset=utf-8'."\r\n";
        $response .= 'Age: '."\r\n";
        $response .= 'Set-Cookie: foo=bar; expires=Mon, 23-Dec-2019 23:50:00 GMT; path=/; domain=localhost'."\r\n";
        $response .= "\r\n";
        $response .= 'supermegafoxyawesomehot';

        $request->shouldReceive('getRawResponse')->andReturn($response);
        $request->shouldReceive('getInfo')->with(CURLINFO_HEADER_SIZE)->andReturn(233);
        $request->shouldReceive('getInfo')->andReturn(array('http_code' => 503));

        $response = Response::forge($request);
        //var_dump($response->getContent());die;
        $this->assertSame('supermegafoxyawesomehot', $response->getContent());
        $this->assertSame('1.1', $response->getProtocolVersion());
        $this->assertSame('utf-8', $response->getCharset());
        $this->assertSame('2013-02-19 12:59:40', $response->getDate()->format('Y-m-d H:i:s'));
        $this->assertSame('nginx', $response->headers->get('Server'));
        $this->assertSame(503, $response->getStatusCode());
        $this->assertSame(true, $response->isServerError());

    }

}
