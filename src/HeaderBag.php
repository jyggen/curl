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

use Jyggen\Curl\RequestInterface;
use Symfony\Component\HttpFoundation\HeaderBag as SymfonyHeaderBag;

/**
 * A container for HTTP headers.
 */
class HeaderBag extends SymfonyHeaderBag
{
    /**
     * The request this bag belongs to.
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * Constructs a `HeaderBag` instance.
     *
     * @param array $headers
     * @param RequestInterface $request
     */
    public function __construct(array $headers, RequestInterface $request)
    {
        $this->request = $request;
        parent::__construct($headers);
    }

    /**
     * Removes a header.
     *
     * @param string $key
     */
    public function remove($key)
    {
        parent::remove($key);
        $this->updateRequest();
    }

    /**
     * Sets a header by name.
     *
     * @param string $key
     * @param string|array $values
     * @param bool $replace
     */
    public function set($key, $values, $replace = true)
    {
        parent::set($key, $values, $replace);
        $this->updateRequest();
    }

    /**
     * Updates the headers in the associated request.
     */
    protected function updateRequest()
    {
        $headers = [];
        foreach ($this->all() as $key => $values) {
            foreach ($values as $value) {
                $headers[] = $key.': '.$value;
            }
        }

        $this->request->setOption(CURLOPT_HTTPHEADER, $headers);
    }
}
