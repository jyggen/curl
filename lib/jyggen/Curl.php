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

namespace jyggen;

use jyggen\Curl\Dispatcher;
use jyggen\Curl\Exception\BadMethodCallException;
use jyggen\Curl\Exception\InvalidArgumentException;
use jyggen\Curl\Session;

class Curl
{

    /**
     * Handle all static helpers.
     * @param  mixed $name
     * @param  array $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {

        $allowedVerbs = array('delete', 'get', 'post', 'put');

        if (in_array($name, $allowedVerbs)) {

            // We require at least one URL.
            if (!isset($arguments[0])) {
                $message = sprintf('Missing argument 1 for %s::%s()', get_called_class(), $name);
                throw new InvalidArgumentException($message);
            }

            $urls = $arguments[0];
            $data = (isset($arguments[1])) ? $arguments[1] : null;

            if (!is_array($urls)) {
                $urls = array($urls => $data);
            } elseif (!(bool)count(array_filter(array_keys($urls), 'is_string'))) {
                foreach ($urls as $key => $url) {
                    $urls[$url] = null;
                    unset($urls[$key]);
                }
            }

            return static::makeRequest(strtoupper($name), $urls, $data);

        } else {
            throw new BadMethodCallException(sprintf('Call to undefined method %s::%s()', get_called_class(), $name));
        }

    }

    /**
     * Setup and execute a HTTP request.
     *
     * @param  string $method
     * @param  array  $urls
     * @return array
     */
    protected static function makeRequest($method, $urls)
    {

        // Create a new Dispatcher.
        $dispatcher = new Dispatcher();

        // Foreach $urls:
        foreach ($urls as $url => $data) {

            if ($data !== null) {
                $data = http_build_query($data);
            }

            // Create a new Session.
            $session = new Session($url);

            // Follow any 3xx HTTP status code.
            $session->setOption(CURLOPT_FOLLOWLOCATION, true);

            switch ($method) {
                case 'DELETE':
                    // Set request method to DELETE.
                    $session->setOption(CURLOPT_CUSTOMREQUEST, 'DELETE');
                    break;
                case 'GET':
                    // Redundant, but reset the method to GET.
                    $session->setOption(CURLOPT_HTTPGET, true);
                    break;
                case 'POST':
                    if ($data !== null) {
                        // Add the POST data to the session.
                        $session->setOption(CURLOPT_POST, true);
                        $session->setOption(CURLOPT_POSTFIELDS, $data);
                    } else {
                        $session->setOption(CURLOPT_CUSTOMREQUEST, 'POST');
                    }
                    break;
                case 'PUT':
                    if ($data !== null) {
                        // Write the PUT data to memory.
                        $fh = fopen('php://temp', 'rw+');
                        fwrite($fh, $data);
                        rewind($fh);

                        // Add the PUT data to the session.
                        $session->setOption(CURLOPT_INFILE, $fh);
                        $session->setOption(CURLOPT_INFILESIZE, strlen($data));
                        $session->setOption(CURLOPT_PUT, true);
                    } else {
                        $session->setOption(CURLOPT_CUSTOMREQUEST, 'PUT');
                    }
                    break;
            }

            // Add the session to the dispatcher.
            $dispatcher->add($session);

        }

        // Execute the request(s).
        $dispatcher->execute();

        $sessions  = $dispatcher->get();
        $responses = array();

        foreach ($sessions as $session) {
            $responses[] = $session->getResponse();
        }

        if (count($urls) === 1) {
            return $responses[0];
        } else {
            return $responses;
        }

    }
}
