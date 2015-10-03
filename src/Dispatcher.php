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

use Closure;
use Jyggen\Curl\DispatcherInterface;
use Jyggen\Curl\Exception\CurlErrorException;
use Jyggen\Curl\Exception\InvalidArgumentException;

/**
 * Dispatcher
 * This class acts as a wrapper around cURL multi functions.
 */
class Dispatcher implements DispatcherInterface
{
    /**
     * The cURL multi handle.
     * @var resource
     */
    protected $handle;

    /**
     * All added requests.
     * @var array
     */
    protected $requests = [];

    /**
     * Stack size.
     * @var  int
     */
    protected $stackSize = 42;

    /**
     * Create a new Dispatcher instance.
     */
    public function __construct()
    {
        $this->handle = curl_multi_init();
    }

    /**
     * Add a Request.
     * @param  RequestInterface $request
     * @return int
     */
    public function add(RequestInterface $request)
    {
        $this->requests[] = $request;
        return (count($this->requests) - 1);
    }

    /**
     * Retrieve all requests.
     * @return array
     */
    public function all()
    {
        return $this->requests;
    }

    /**
     * Remove all requests.
     * @return void
     */
    public function clear()
    {
        $this->requests = [];
    }

    /**
     * Execute all added requests.
     * @return void
     */
    public function execute(callable $callback = null)
    {
        $stacks = $this->buildStacks();

        foreach ($stacks as $requests) {
            // Tell each request to use this dispatcher.
            foreach ($requests as $request) {
                $status = curl_multi_add_handle($this->handle, $request->getHandle());
                if ($status !== CURLM_OK) {
                    throw new CurlErrorException(sprintf(
                        'Unable to add request to cURL multi handle (code #%u)',
                        $status
                    ));
                }
            }

            // Start dispatching the requests.
            $this->dispatch();

            // Loop through all requests and remove their relationship to our dispatcher.
            foreach ($requests as $request) {
                if ($request->isSuccessful() === false) {
                    throw new CurlErrorException($request->getErrorMessage());
                }

                $request->setRawResponse(curl_multi_getcontent($request->getHandle()));
                curl_multi_remove_handle($this->handle, $request->getHandle());

                if ($callback !== null) {
                    $callback($request->getResponse());
                }
            }
        }
    }

    /**
     * Retrieve a specific request.
     * @param  int   $key null
     * @return mixed
     * @deprecated Calling this method without a key is deprecated, use Dispatcher::all() instead.
     */
    public function get($key = null)
    {
        // Return all requests if no key is specified.
        if ($key === null) {
            return $this->requests;
        }

        // Otherwise, if the key exists; return that request, else return null.
        return (isset($this->requests[$key])) ? $this->requests[$key] : null;
    }

    /**
     * Retrieve currently used stack size.
     * @return int
     */
    public function getStackSize()
    {
        return $this->stackSize;
    }

    /**
     * Remove a specific request.
     * @param  int  $key
     * @return void
     */
    public function remove($key)
    {
        // Make sure the request exists before we try to remove it.
        if (array_key_exists($key, $this->requests)) {
            $this->requests[$key]->removeMultiHandle($this->handle);
            unset($this->requests[$key]);
        }
    }

    /**
     * Set stack size.
     * @return void
     */
    public function setStackSize($size)
    {
        if (gettype($size) !== 'integer') {
            throw new InvalidArgumentException('setStackSize() expected an integer, '.gettype($size).' received.');
        }

        $this->stackSize = $size;
    }

    /**
     * Build stacks of requests.
     * @return array
     */
    protected function buildStacks()
    {
        $stacks   = [];
        $stackNo  = 0;
        $currSize = 0;

        foreach ($this->requests as $request) {
            if ($currSize === $this->stackSize) {
                $currSize = 0;
                $stackNo++;
            }

            $stacks[$stackNo][] = $request;
            $currSize++;
        }

        return $stacks;
    }

    /**
     * Dispatch the requests.
     * @return void
     */
    protected function dispatch()
    {
        // Start processing the requests.
        list($mrc, $active) = $this->process();

        // Keep processing requests until we're done.
        while ($active and $mrc === CURLM_OK) {
            // Process the next request.
            list($mrc, $active) = $this->process();
        }

        // Throw an exception if something went wrong.
        if ($mrc !== CURLM_OK) {
            throw new CurlErrorException('cURL read error #'.$mrc);
        }
    }

    /**
     * Process requests.
     * @return array
     */
    protected function process()
    {
        // Workaround for PHP Bug #61141.
        if (curl_multi_select($this->handle) === -1) {
            usleep(100);
        }

        do {
            $mrc = curl_multi_exec($this->handle, $active);
        } while ($mrc === CURLM_CALL_MULTI_PERFORM);

        return [$mrc, $active];
    }
}
