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
use jyggen\Curl\Exception\CurlErrorException;
use jyggen\Curl\Exception\ProtectedOptionException;
use jyggen\Curl\Response;
use jyggen\Curl\SessionInterface;

class Session implements SessionInterface {

	/**
	 * Content returned from an execution.
	 *
	 * @var  string
	 */
	protected $content;

	/**
	 * Defaults options that can't be overwritten.
	 *
	 * @var array
	 */
	protected $defaults = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HEADER         => true,
	);

	/**
	 * If the session has been executed.
	 * @var boolean
	 */
	protected $executed = false;

	/**
	 * The cURL handle.
	 *
	 * @var curl
	 */
	protected $handle;

	/**
	 * Number of cURL multi handles attached to the session.
	 *
	 * @var int
	 */
	protected $multiNo = 0;

	/**
	 * The Response object.
	 *
	 * @var Response
	 */
	protected $response;

	/**
	 * Create a new Session instance.
	 *
	 * @param  string $url
	 * @return void
	 */
	public function __construct($url)
	{

		$this->handle = curl_init($url);

		foreach ($this->defaults as $option => $value) {

			curl_setopt($this->handle, $option, $value);

		}

	}

	/**
	 * Shutdown sequence.
	 *
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
	 * @return curl
	 */
	public function getHandle()
	{

		return $this->handle;

	}

	/**
	 * Get information regarding the session.
	 *
	 * @param  int   $key null
	 * @return mixed
	 */
	public function getInfo($key = null)
	{

		// If no key is supplied return all available information.
		if ($key === null) {

			return curl_getinfo($this->handle);

		// Otherwise retrieve information for the specified key.
		} else {

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
	 * Set an option for the session.
	 *
	 * @param  mixed            $option
	 * @param  mixed            $value  null
	 * @return SessionInterface
	 */
	public function setOption($option, $value = null)
	{

		// If it's an array, loop through each option and call this method recursively.
		if (is_array($option)) {
			foreach ($option as $opt => $val) {
				$this->setOption($opt, $val);
			}
		// Else if it isn't a default value, call curl_setopt and throw an exception on failure.
		} elseif (!array_key_exists($option, $this->defaults)) {
			if (curl_setopt($this->handle, $option, $value) === false) {
				throw new CurlErrorException(sprintf('Couldn\'t set option #%u', $option));
			}
		// Else it's a protected default value and shouldn't be overwritten, throw an exception!
		} else {
			throw new ProtectedOptionException(sprintf('Unable to set protected option #%u', $option));
		}

	}

	/**
	 * Add this session to the supplied cURL multi handle.
	 *
	 * @param  curl_multi $multiHandle
	 * @return int
	 */
	public function addMultiHandle($multiHandle)
	{

		// If it's a curl_multi resource add this session to it and throw an exception on failure.
		if (is_resource($multiHandle) and get_resource_type($multiHandle) === 'curl_multi') {

			$status = curl_multi_add_handle($multiHandle, $this->handle);

			if ($status === CURLM_OK) {

				$this->multiNo++;
				return true;

			} else {

				throw new CurlErrorException(sprintf('Unable to add session to cURL multi handle (code #%u)', $msg));

			}
		// Otherwise throw an exception!
		} else {

			throw new CurlErrorException(sprintf('Expects parameter 1 to be a curl_multi resource, %s given', gettype($multiHandle)));

		}

	}

	/**
	 * Execute the request.
	 *
	 * @return void
	 */
	public function execute()
	{

		// If the session is attached to a multi handle it has already been
		// executed so all we have to do is to retrieve the response.
		if ($this->hasMulti()) {

			$this->content = curl_multi_getcontent($this->handle);

		// Otherwise we execute the request now and retrieve the response.
		} else {

			$this->content = curl_exec($this->handle);

		}

		// If the execution was successful flag it as executed.
		if ($this->isSuccessful()) {

			$this->executed = true;

		// Otherwise throw an exception.
		} else {

			throw CurlErrorException($this->getErrorMessage());

		}

	}

	/**
	 * If the session is attached to one or more cURL multi handles.
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
	 * Remove the session from a cURL multi handle.
	 *
	 * @param  curl_multi $multiHandle
	 * @return int
	 */
	public function removeMultiHandle($multiHandle)
	{

		$this->multiNo--;

		return curl_multi_remove_handle($multiHandle, $this->handle);

	}

}
