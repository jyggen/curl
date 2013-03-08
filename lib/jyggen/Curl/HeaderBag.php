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

namespace jyggen\Curl;

use jyggen\Curl\SessionInterface;

class HeaderBag extends \Symfony\Component\HttpFoundation\HeaderBag {

    /**
     * @var \jyggen\Curl\SessionInterface
     */
    protected $session;

    /**
     * Constructor.
     *
     * @param array $headers
     */
    public function __construct(array $headers, SessionInterface $session)
    {

        $this->session = $session;
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
        $this->updateSession();

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
        $this->updateSession();

    }

    protected function updateSession()
    {

        $headers = array();
        foreach ($this->all() as $key => $values) {
            foreach ($values as $value) {
                $headers[] = $key.': '.$value;
            }
        }

        $this->session->setOption(CURLOPT_HTTPHEADER, $headers);

    }

}