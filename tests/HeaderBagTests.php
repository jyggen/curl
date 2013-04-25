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

use jyggen\Curl\HeaderBag;
use Mockery as m;

class HeaderBagTests extends PHPUnit_Framework_TestCase
{

    public function teardown()
    {

        m::close();

    }

    public function testConstruct()
    {

        $session   = m::mock('jyggen\\Curl\\SessionInterface');
        $headerbag = new HeaderBag(array(), $session);
        $this->assertInstanceof('jyggen\\Curl\\HeaderBag', $headerbag);

    }

    public function testSet()
    {

        $phpunit   = $this;
        $session   = m::mock('jyggen\\Curl\\SessionInterface');
        $headerbag = new HeaderBag(array(), $session);
        $session->shouldReceive('setOption')->times(1)->with(m::mustBe(CURLOPT_HTTPHEADER), m::type('array'));
        $headerbag->set('foo', 'bar');

    }

    public function testRemove()
    {

        $phpunit   = $this;
        $session   = m::mock('jyggen\\Curl\\SessionInterface');
        $headerbag = new HeaderBag(array(), $session);
        $session->shouldReceive('setOption')->times(1)->with(m::mustBe(CURLOPT_HTTPHEADER), m::type('array'));
        $headerbag->remove('foo');

    }

}