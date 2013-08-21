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

namespace jyggen\Curl;

use jyggen\Curl\RequestInterface;

/**
 * HeaderBag
 *
 * This is a container for HTTP headers.
 */
class HeaderBag extends \Symfony\Component\HttpFoundation\HeaderBag
{
    /**
     * Which request the instance belongs to.
     *
     * @var \jyggen\Curl\RequestInterface
     */
    protected $request;

    /**
     * Constructor.
     *
     * @param array            $headers
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
     * @param string  $key
     * @param mixed   $values
     * @param Boolean $replace
     */
    public function set($key, $values, $replace = true)
    {

        parent::set($key, $values, $replace);
        $this->updateRequest();

    }

    /**
     * Update the associated request with the values of this container.
     * @return void
     */
    protected function updateRequest()
    {

        $headers = array();
        foreach ($this->all() as $key => $values) {
            foreach ($values as $value) {
                $headers[] = $key.': '.$value;
            }
        }

        $this->request->setOption(CURLOPT_HTTPHEADER, $headers);

    }
}
