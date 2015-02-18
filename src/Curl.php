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

use Jyggen\Curl\Dispatcher;
use Jyggen\Curl\DispatcherInterface;
use Jyggen\Curl\Exception\InvalidArgumentException;
use Jyggen\Curl\Request;
use Jyggen\Curl\RequestInterface;

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

    public static function delete($urls, $data = null, $callback = null)
    {
        return static::make('delete', $urls, $data, $callback);
    }

    public static function get($urls, $data = null, $callback = null)
    {
        return static::make('get', $urls, $data, $callback);
    }

    public static function post($urls, $data = null, $callback = null)
    {
        return static::make('post', $urls, $data, $callback);
    }

    public static function put($urls, $data = null, $callback = null)
    {
        return static::make('put', $urls, $data, $callback);
    }

    /**
     * Handle all static helpers.
     * @return array
     */
    protected static function make($verb, $urls, $data, $callback)
    {
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

        new static($verb, $dispatcher, $requests, $dataStore, $callback);

        $requests  = $dispatcher->all();
        $responses = array();

        foreach ($requests as $request) {
            $responses[] = $request->getResponse();
        }

        return $responses;
    }

    /**
     * Create a new Curl instance.
     *
     * @param  string              $method
     * @param  DispatcherInterface $dispatcher
     * @param  array               $requests
     * @param  array               $data
     */
    protected function __construct($method, DispatcherInterface $dispatcher, array $requests, array $data, $callback)
    {

        $this->dispatcher = $dispatcher;
        $this->method     = strtoupper($method);

        foreach ($requests as $key => $request) {
            $this->requests[] = $request;
            $this->data[$key] = $data[$key];
        }

        $this->makeRequest($callback);

    }

    /**
     * Setup and execute a HTTP request.
     *
     * @return void
     */
    protected function makeRequest($callback)
    {

         // Foreach request:
        foreach ($this->requests as $key => $request) {

            $data = (isset($this->data[$key]) and $this->data[$key] !== null) ? $this->data[$key] : null;

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
        if ($callback !== null) {
            $this->dispatcher->execute($callback);
        } else {
            $this->dispatcher->execute();
        }

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
        $request->setOption(CURLOPT_CUSTOMREQUEST, 'PUT');
        if ($data !== null) {
            $request->setOption(CURLOPT_POSTFIELDS, $data);
        }
    }
}
