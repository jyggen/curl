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

namespace jyggen;

use jyggen\Curl\Dispatcher;
use jyggen\Curl\DispatcherInterface;
use jyggen\Curl\Exception\BadMethodCallException;
use jyggen\Curl\Exception\InvalidArgumentException;
use jyggen\Curl\Session;
use jyggen\Curl\SessionInterface;

/**
 * Curl
 *
 * This class provides static helpers for simplified cURL usage.
 */
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

            $dispatcher = new Dispatcher;
            $sessions   = array();
            $dataStore  = array();

            foreach ($urls as $url => $data) {
                $sessions[]  = new Session($url);
                $dataStore[] = $data;
            }

            $curl      = new static($name, $dispatcher, $sessions, $dataStore);
            $sessions  = $dispatcher->get();
            $responses = array();

            foreach ($sessions as $session) {
                $responses[] = $session->getResponse();
            }

            return (count($sessions) === 1) ? $responses[0] : $responses;

        } else {
            throw new BadMethodCallException(sprintf('Call to undefined method %s::%s()', get_called_class(), $name));
        }

    }

    /**
     * An array of data used by the sessions.
     *
     * @var array
     */
    protected $data;

    /**
     * Instance of Dispatcher to use.
     *
     * @var DispatcherInterface
     */
    protected $dispatcher;

    /**
     * Which HTTP verb to use.
     *
     * @var string
     */
    protected $method;

    /**
     * Array of sessions to execute.
     *
     * @var array
     */
    protected $sessions;

    /**
     * Create a new Curl instance.
     *
     * @param  string              $method
     * @param  DispatcherInterface $dispatcher
     * @param  array               $sessions
     * @param  array               $data
     * @return void
     */
    public function __construct($method, DispatcherInterface $dispatcher, array $sessions, array $data)
    {

        $this->dispatcher = $dispatcher;
        $this->method     = strtoupper($method);

        foreach ($sessions as $key => $session) {
            if ($session instanceof SessionInterface) {
                $this->sessions[] = $session;
                $this->data[$key] = $data[$key];
            } else {
                $msg = 'Session #%u must implement SessionInterface';
                throw new InvalidArgumentException(sprintf($msg, $key, gettype($session)));
            }
        }

        $this->makeRequest();

    }

    /**
     * Setup and execute a HTTP request.
     *
     * @return void
     */
    protected function makeRequest()
    {

         // Foreach session:
        foreach ($this->sessions as $key => $session) {

            if (isset($this->data[$key]) and $this->data[$key] !== null) {
                $data = http_build_query($this->data[$key]);
            } else {
                $data = null;
            }

            // Follow any 3xx HTTP status code.
            $session->setOption(CURLOPT_FOLLOWLOCATION, true);

            switch ($this->method) {
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
            $this->dispatcher->add($session);

        }

        // Execute the request(s).
        $this->dispatcher->execute();

    }
}
