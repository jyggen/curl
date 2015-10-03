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

namespace Jyggen\Curl;

use Jyggen\Curl\HeaderBag;
use Mockery as m;

class HeaderBagTest extends \PHPUnit_Framework_TestCase
{

    public function teardown()
    {

        m::close();

    }

    public function testConstruct()
    {

        $request   = m::mock('Jyggen\\Curl\\RequestInterface');
        $headerbag = new HeaderBag(array(), $request);
        $this->assertInstanceof('Jyggen\\Curl\\HeaderBag', $headerbag);

    }

    public function testSet()
    {

        $phpunit   = $this;
        $request   = m::mock('Jyggen\\Curl\\RequestInterface');
        $headerbag = new HeaderBag(array(), $request);
        $request->shouldReceive('setOption')->times(1)->with(m::mustBe(CURLOPT_HTTPHEADER), m::type('array'));
        $headerbag->set('foo', 'bar');

    }

    public function testRemove()
    {

        $phpunit   = $this;
        $request   = m::mock('Jyggen\\Curl\\RequestInterface');
        $headerbag = new HeaderBag(array(), $request);
        $request->shouldReceive('setOption')->times(1)->with(m::mustBe(CURLOPT_HTTPHEADER), m::type('array'));
        $headerbag->remove('foo');

    }

}
