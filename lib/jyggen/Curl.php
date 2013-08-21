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

namespace jyggen;

use jyggen\Curl\Dispatcher;
use jyggen\Curl\DispatcherInterface;
use jyggen\Curl\Exception\BadMethodCallException;
use jyggen\Curl\Exception\InvalidArgumentException;
use jyggen\Curl\Request;
use jyggen\Curl\RequestInterface;

/**
 * Curl
 *
 * This class provides static helpers for simplified cURL usage.
 */
class Curl
{
    /**
     * An array of data used by the requests.
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
     * Array of requests to execute.
     *
     * @var array
     */
    protected $requests;

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
            $requests   = array();
            $dataStore  = array();

            foreach ($urls as $url => $data) {
                $requests[]  = new Request($url);
                $dataStore[] = $data;
            }

            new static($name, $dispatcher, $requests, $dataStore);

            $requests  = $dispatcher->get();
            $responses = array();

            foreach ($requests as $request) {
                $responses[] = $request->getResponse();
            }

            return $responses;

        } else {
            throw new BadMethodCallException(sprintf('Call to undefined method %s::%s()', get_called_class(), $name));
        }

    }

    /**
     * Create a new Curl instance.
     *
     * @param  string              $method
     * @param  DispatcherInterface $dispatcher
     * @param  array               $requests
     * @param  array               $data
     * @return void
     */
    public function __construct($method, DispatcherInterface $dispatcher, array $requests, array $data)
    {

        $this->dispatcher = $dispatcher;
        $this->method     = strtoupper($method);

        foreach ($requests as $key => $request) {
            if ($request instanceof RequestInterface) {
                $this->requests[] = $request;
                $this->data[$key] = $data[$key];
            } else {
                $msg = 'Request #%u must implement RequestInterface';
                throw new InvalidArgumentException(sprintf($msg, $key, gettype($request)));
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

         // Foreach request:
        foreach ($this->requests as $key => $request) {

            if (isset($this->data[$key]) and $this->data[$key] !== null) {
                $data = http_build_query($this->data[$key]);
            } else {
                $data = null;
            }

            // Follow any 3xx HTTP status code.
            $request->setOption(CURLOPT_FOLLOWLOCATION, true);

            switch ($this->method) {
                case 'DELETE':
                    $this->prepareDeleteRequest($request);
                    break;
                case 'GET':
                    $this->prepareGetRequest($request);
                    break;
                case 'POST':
                    $this->preparePostRequest($request, $data);
                    break;
                case 'PUT':
                    $this->preparePutRequest($request, $data);
                    break;
            }

            // Add the request to the dispatcher.
            $this->dispatcher->add($request);

        }

        // Execute the request(s).
        $this->dispatcher->execute();

    }

    protected function prepareDeleteRequest(RequestInterface $request)
    {
        $request->setOption(CURLOPT_CUSTOMREQUEST, 'DELETE'); // Set request method to DELETE.
    }

    protected function prepareGetRequest(RequestInterface $request)
    {
        $request->setOption(CURLOPT_HTTPGET, true); // Redundant, but reset the method to GET.
    }

    protected function preparePostRequest(RequestInterface $request, $data)
    {
        if ($data !== null) {
            // Add the POST data to the request.
            $request->setOption(CURLOPT_POST, true);
            $request->setOption(CURLOPT_POSTFIELDS, $data);
        } else {
            $request->setOption(CURLOPT_CUSTOMREQUEST, 'POST');
        }
    }

    protected function preparePutRequest(RequestInterface $request, $data)
    {
        if ($data !== null) {
            // Write the PUT data to memory.
            $file = fopen('php://temp', 'rw+');
            fwrite($file, $data);
            rewind($file);

            // Add the PUT data to the request.
            $request->setOption(CURLOPT_INFILE, $file);
            $request->setOption(CURLOPT_INFILESIZE, strlen($data));
            $request->setOption(CURLOPT_PUT, true);
        } else {
            $request->setOption(CURLOPT_CUSTOMREQUEST, 'PUT');
        }
    }
}
