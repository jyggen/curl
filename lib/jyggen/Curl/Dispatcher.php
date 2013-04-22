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

use jyggen\Curl\DispatcherInterface;
use jyggen\Curl\Exception\CurlErrorException;
use jyggen\Curl\RequestnInterface;

/**
 * Dispatcher
 *
 * This class acts as a wrapper around cURL multi functions.
 */
class Dispatcher implements DispatcherInterface
{

    /**
     * The cURL multi handle.
     *
     * @var curl_multi
     */
    protected $handle;

    /**
     * All added requests.
     *
     * @var array
     */
    protected $requests = array();

    /**
     * Create a new Dispatcher instance.
     *
     * @return void
     */
    public function __construct()
    {

        $this->handle = curl_multi_init();

    }

    /**
     * Add a REquest.
     *
     * @param  RequestInterface $request
     * @return int
     */
    public function add(RequestInterface $request)
    {

        // Tell the $request to use this handle.
        $request->addMultiHandle($this->handle);

        // Store the request.
        $this->requests[] = $request;

        // Return the request's key.
        return (count($this->requests) - 1);

    }

    /**
     * Remove all requests.
     *
     * @return void
     */
    public function clear()
    {

        // Loop through all requests and remove
        // their relationship to our handle.
        foreach ($this->requests as $request) {

            $request->removeMultiHandle($this->handle);

        }

        // Reset the requests array.
        $this->requests = array();

    }

    /**
     * Execute all added requests.
     *
     * @return void
     */
    public function execute()
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

        // Otherwise everything went okay, retrieve the data from each request.
        foreach ($this->requests as $key => $request) {
            $request->execute();
            $request->removeMultiHandle($this->handle);
        }

    }

    /**
     * Retrieve all or a specific request.
     *
     * @param  int   $key null
     * @return mixed
     */
    public function get($key = null)
    {

        // Return all requests if no key is specified.
        if ($key === null) {

            return $this->requests;

        } else { // Otherwise, if the key exists; return that request, else return null.

            return (isset($this->requests[$key])) ? $this->requests[$key] : null;

        }

    }

    /**
     * Remove a specific request.
     *
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

        return array($mrc, $active);

    }
}
