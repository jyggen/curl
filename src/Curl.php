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

use Jyggen\Curl\Dispatcher;
use Jyggen\Curl\DispatcherInterface;
use Jyggen\Curl\Exception\InvalidArgumentException;
use Jyggen\Curl\Request;
use Jyggen\Curl\RequestInterface;

/**
 * Provides static helpers for simplified cURL usage.
 */
class Curl
{
    /**
     * The data to send with each request.
     *
     * @var array
     */
    protected $data;

    /**
     * The request dispatcher to use.
     *
     * @var DispatcherInterface
     */
    protected $dispatcher;

    /**
     * Which HTTP verb to use.
     *
     * @var string
     */
    protected $verb;

    /**
     * The requests that should be sent.
     *
     * @var array
     */
    protected $requests;

    /**
     * Make one or multiple DELETE requests.
     *
     * @param mixed $urls
     * @param mixed $data
     * @param callable $callback
     * @return array
     */
    public static function delete($urls, $data = null, callable $callback = null)
    {
        return static::make('delete', $urls, $data, $callback);
    }

    /**
     * Make one or multiple GET requests.
     *
     * @param mixed $urls
     * @param mixed $data
     * @param callable $callback
     * @return array
     */
    public static function get($urls, $data = null, callable $callback = null)
    {
        return static::make('get', $urls, $data, $callback);
    }

    /**
     * Make one or multiple POST requests.
     *
     * @param mixed $urls
     * @param mixed $data
     * @param callable $callback
     * @return array
     */
    public static function post($urls, $data = null, callable $callback = null)
    {
        return static::make('post', $urls, $data, $callback);
    }

    /**
     * Make one or multiple PUT requests.
     *
     * @param mixed $urls
     * @param mixed $data
     * @param callable $callback
     * @return array
     */
    public static function put($urls, $data = null, callable $callback = null)
    {
        return static::make('put', $urls, $data, $callback);
    }

    /**
     * Make one or multiple requests.
     *
     * @param string $verb
     * @param mixed $urls
     * @param mixed $data
     * @param callable $callback
     * @return array
     */
    protected static function make($verb, $urls, $data, callable $callback = null)
    {
        if (!is_array($urls)) {
            $urls = [$urls => $data];
        } elseif (!(bool)count(array_filter(array_keys($urls), 'is_string'))) {
            foreach ($urls as $key => $url) {
                $urls[$url] = null;
                unset($urls[$key]);
            }
        }

        $dispatcher = new Dispatcher;
        $requests   = [];
        $dataStore  = [];

        foreach ($urls as $url => $data) {
            $requests[]  = new Request($url);
            $dataStore[] = $data;
        }

        new static($verb, $dispatcher, $requests, $dataStore, $callback);

        $requests  = $dispatcher->all();
        $responses = [];

        foreach ($requests as $request) {
            $responses[] = $request->getResponse();
        }

        return $responses;
    }

    /**
     * Constructs a `Curl` instance.
     *
     * @param string $verb
     * @param DispatcherInterface $dispatcher
     * @param array $requests
     * @param array $data
     * @param callable $callback
     */
    protected function __construct(
        $verb,
        DispatcherInterface $dispatcher,
        array $requests,
        array $data,
        callable $callback = null
    ) {
        $this->dispatcher = $dispatcher;
        $this->verb       = strtoupper($verb);

        foreach ($requests as $key => $request) {
            $this->requests[] = $request;
            $this->data[$key] = $data[$key];
        }

        $this->makeRequest($callback);
    }

    /**
     * Prepares and sends HTTP requests.
     *
     * @param callable $callback
     */
    protected function makeRequest(callable $callback = null)
    {
        // Foreach request:
        foreach ($this->requests as $key => $request) {
            $data = (isset($this->data[$key]) and $this->data[$key] !== null) ? $this->data[$key] : null;

            // Follow any 3xx HTTP status code.
            $request->setOption(CURLOPT_FOLLOWLOCATION, true);

            switch ($this->verb) {
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

    /**
     * Sets a request's HTTP verb to DELETE.
     *
     * @param RequestInterface $request
     */
    protected function prepareDeleteRequest(RequestInterface $request)
    {
        $request->setOption(CURLOPT_CUSTOMREQUEST, 'DELETE'); // Set request verb to DELETE.
    }

    /**
     * Sets a request's HTTP verb to GET.
     *
     * @param RequestInterface $request
     */
    protected function prepareGetRequest(RequestInterface $request)
    {
        $request->setOption(CURLOPT_HTTPGET, true); // Redundant, but reset the verb to GET.
    }

    /**
     * Sets a request's HTTP verb to POST.
     *
     * @param RequestInterface $request
     */
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

    /**
     * Sets a request's HTTP verb to PUT.
     *
     * @param RequestInterface $request
     */
    protected function preparePutRequest(RequestInterface $request, $data)
    {
        $request->setOption(CURLOPT_CUSTOMREQUEST, 'PUT');
        if ($data !== null) {
            $request->setOption(CURLOPT_POSTFIELDS, $data);
        }
    }
}
