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
