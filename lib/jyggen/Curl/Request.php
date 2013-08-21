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

namespace jyggen\Curl;

use jyggen\Curl\Exception\CurlErrorException;
use jyggen\Curl\Exception\ProtectedOptionException;
use jyggen\Curl\HeaderBag;
use jyggen\Curl\Response;
use jyggen\Curl\RequestInterface;

/**
 * Request
 *
 * This class acts as a wrapper around cURL functions.
 */
class Request implements RequestInterface
{
    /**
     * A container of headers.
     *
     * @var \jyggen\Curl\HeaderBag
     */
    public $headers;

    /**
     * The raw response body.
     *
     * @var string
     */
    protected $content;

    /**
     * Protected default values that can't be changed.
     * These are required for this library to work correctly.
     *
     * @var array
     */
    protected $defaults = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
    );

    /**
     * If this request has been executed.
     *
     * @var boolean
     */
    protected $executed = false;

    /**
     * The cURL resource attached.
     *
     * @var curl
     */
    protected $handle;

    /**
     * Number of cURL multi handles attached.
     *
     * @var integer
     */
    protected $multiNo = 0;

    /**
     * Response object.
     *
     * @var \jyggen\Curl\Response
     */
    protected $response;

    /**
     * Create a new Request instance.
     *
     * @param  string $url
     * @return void
     */
    public function __construct($url)
    {

        $this->handle  = curl_init($url);
        $this->headers = new HeaderBag(array(), $this);

        foreach ($this->defaults as $option => $value) {

            curl_setopt($this->handle, $option, $value);

        }

    }

    /**
     * Retrieve the latest error.
     *
     * @return string
     */
    public function getErrorMessage()
    {

        $error = curl_error($this->handle);

        return ($error === '') ? null : $error;

    }

    /**
     * Retrieve the cURL handle.
     *
     * @return curl
     */
    public function getHandle()
    {

        return $this->handle;

    }

    /**
     * Get information regarding the request.
     *
     * @param  int   $key null
     * @return mixed
     */
    public function getInfo($key = null)
    {

        if ($key === null) { // If no key is supplied return all available information.

            return curl_getinfo($this->handle);

        } else { // Otherwise retrieve information for the specified key.

            return curl_getinfo($this->handle, $key);

        }

    }

    /**
     * Get the raw response.
     *
     * @return string
     */
    public function getRawResponse()
    {

        return $this->content;

    }

    /**
     * Get the response.
     *
     * @return array
     */
    public function getResponse()
    {

        if ($this->response === null and $this->isExecuted()) {

            $this->response = Response::forge($this);

        }

        return $this->response;

    }

    /**
     * Set an option for the request.
     *
     * @param  mixed            $option
     * @param  mixed            $value  null
     * @return RequestInterface
     */
    public function setOption($option, $value = null)
    {

        if (is_array($option)) { // If it's an array, loop through each option and call this method recursively.
            foreach ($option as $opt => $val) {
                $this->setOption($opt, $val);
            }
        } elseif (!array_key_exists($option, $this->defaults)) { // Else if it isn't a default value.
            if (curl_setopt($this->handle, $option, $value) === false) {
                throw new CurlErrorException(sprintf('Couldn\'t set option #%u', $option));
            }
        } else { // Else it's a protected default value and shouldn't be overwritten, throw an exception!
            throw new ProtectedOptionException(sprintf('Unable to set protected option #%u', $option));
        }

    }

    /**
     * Add this request to the supplied cURL multi handle.
     *
     * @param  curl_multi $multiHandle
     * @return int
     */
    public function addMultiHandle($multiHandle)
    {

        // If it's a curl_multi resource add this request to it and throw an exception on failure.
        if ($this->isValidMultiHandle($multiHandle)) {

            $status = curl_multi_add_handle($multiHandle, $this->handle);

            if ($status !== CURLM_OK) {

                throw new CurlErrorException(sprintf('Unable to add request to cURL multi handle (code #%u)', $status));

            }

            $this->multiNo++;
            return true;

        } else { // Otherwise throw an exception!

            $message = sprintf('Expects parameter 1 to be a curl_multi resource, %s given', gettype($multiHandle));
            throw new CurlErrorException($message);

        }

    }

    /**
     * Execute the request.
     *
     * @return void
     */
    public function execute()
    {

        // If the request is attached to a multi handle it has already been
        // executed so all we have to do is to retrieve the response.
        if ($this->hasMulti()) {

            $this->content = curl_multi_getcontent($this->handle);

        } else { // Otherwise we execute the request now and retrieve the response.

            $this->content = curl_exec($this->handle);

        }

        // If the execution was successful flag it as executed.
        if ($this->isSuccessful()) {

            $this->executed = true;

        } else { // Otherwise throw an exception.

            throw new CurlErrorException($this->getErrorMessage());

        }

    }

    /**
     * If the request is attached to one or more cURL multi handles.
     * @return boolean
     */
    public function hasMulti()
    {

        return ($this->multiNo > 0) ? true : false;

    }

    /**
     * If the request has been executed.
     *
     * @return boolean
     */
    public function isExecuted()
    {

        return ($this->executed) ? true : false;

    }

    /**
     * If the request was successful.
     *
     * @return boolean
     */
    public function isSuccessful()
    {

        return ($this->getErrorMessage() === null) ? true : false;

    }

    /**
     * Remove the request from a cURL multi handle.
     *
     * @param  curl_multi $multiHandle
     * @return int
     */
    public function removeMultiHandle($multiHandle)
    {

        $this->multiNo--;

        return curl_multi_remove_handle($multiHandle, $this->handle);

    }

    /**
     * Check if the argument is a valid cURL multi handle.
     *
     * @param  mixed  $multiHandle
     * @return boolean
     */
    protected function isValidMultiHandle($multiHandle)
    {

        return (is_resource($multiHandle) and get_resource_type($multiHandle) === 'curl_multi');

    }
}
