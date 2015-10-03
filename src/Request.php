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

use Jyggen\Curl\Exception\CurlErrorException;
use Jyggen\Curl\Exception\ProtectedOptionException;
use Jyggen\Curl\HeaderBag;
use Jyggen\Curl\Response;
use Jyggen\Curl\RequestInterface;

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
     * @var \Jyggen\Curl\HeaderBag
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
    protected $defaults = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
    ];

    /**
     * If this request has been executed.
     *
     * @var boolean
     */
    protected $executed = false;

    /**
     * The cURL resource attached.
     *
     * @var resource
     */
    protected $handle;

    /**
     * Response object.
     *
     * @var \Jyggen\Curl\Response
     */
    protected $response;

    /**
     * Create a new Request instance.
     *
     * @param  string $url
     */
    public function __construct($url)
    {
        $this->handle  = curl_init($url);
        $this->headers = new HeaderBag([], $this);

        foreach ($this->defaults as $option => $value) {
            curl_setopt($this->handle, $option, $value);
        }
    }

    /**
     * Shutdown sequence.
     * @return void
     */
    public function __destruct()
    {
        curl_close($this->handle);
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
     * @return resource
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
        }
        return curl_getinfo($this->handle, $key);
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
            return;
        }

        if (array_key_exists($option, $this->defaults)) { // If it is a default value.
            throw new ProtectedOptionException(sprintf('Unable to set protected option #%u', $option));
        }

        if (curl_setopt($this->handle, $option, $value) === false) {
            throw new CurlErrorException(sprintf('Couldn\'t set option #%u', $option));
        }
    }

    /**
     * Execute the request.
     *
     * @return void
     */
    public function execute()
    {
        $this->content = curl_exec($this->handle);

        // If the execution wasn't successful, throw an exception.
        if ($this->isSuccessful() === false) {
            throw new CurlErrorException($this->getErrorMessage());
        }

        $this->executed = true;
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

    public function setRawResponse($content)
    {
        $this->executed = true;
        $this->content  = $content;
    }
}
