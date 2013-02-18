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

use jyggen\Curl\Response;

class Session implements SessionInterface
{

	/**
	 * Content returned from an execute.
	 *
	 * @var  string
	 */
	protected $content;

	/**
	 * A list of defaults options that can't be overwritten.
	 *
	 * @var array
	 */
	protected $defaults = array(CURLOPT_RETURNTRANSFER => true);

	/**
	 * If the session is attached to a cURL multi handle.
	 *
	 * @var boolean
	 */
	protected $isMulti  = false;

	/**
	 * The cURL handle.
	 *
	 * @var curl
	 */
	protected $handle;

	/**
	 * The Response object.
	 *
	 * @var jyggen\Curl\Response
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
	 * Retrieve the latest error.
	 *
	 * @return string
	 */
	public function getErrorMessage()
	{

		return curl_error($this->handle);

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
	 * @param  int $key null
	 * @return mixed
	 */
	public function getInfo($key = null)
	{

		// If $key is null:
		if ($key === null) {

			return curl_getinfo($this->handle);

		// Else:
		} else {

			return curl_getinfo($this->handle, $key);

		}

	}

	/**
	 * Get this session's response.
	 *
	 * @return array
	 */
	public function getResponse()
	{

		if ($this->response === null and is_string($this->content)) {

			$this->response = Response::forge($this, $this->content);

		}

		return $this->response;

	}

	/**
	 * Set an option for the session.
	 *
	 * @param  mixed $option
	 * @param  mixed $value  null
	 * @return jyggen\Curl\Session
	 */
	public function setOption($option, $value = null)
	{

		// $option is an array, loop through each option and call this method recursively.
		if (is_array($option)) {

			foreach ($option as $opt => $val) {
				$this->setOption($opt, $val);
			}

		// $option isn't a default value, call curl_setopt and throw an exception on false.
		} elseif (!array_key_exists($option, $this->defaults)) {

			if (curl_setopt($this->handle, $option, $value) === false) {

				throw new \jyggen\CurlErrorException(sprintf('Couldn\'t set option #%u.', $option));

			}

		// $option is a protected default value and shouldn't be overwritten, throw an exception!
		} else {

			throw new \jyggen\ProtectedOptionException('To prevent unexpected behavior you are not allowed to change option #'.$option.'.');

		}

	}

	/**
	 * Add the session to a cURL multi handle.
	 *
	 * @param  curl_multi $multiHandle
	 * @return int
	 */
	public function addMultiHandle($multiHandle)
	{

		$this->isMulti = true;

		return curl_multi_add_handle($multiHandle, $this->handle);

	}

	/**
	 * Execute the request.
	 *
	 * @return void
	 */
	public function execute()
	{

		if ($this->isMulti) {

			$this->content = curl_multi_getcontent($this->handle);

		} else {

			$this->content = curl_exec($this->handle);

		}

	}

	/**
	 * If the request was successful or not.
	 *
	 * @return boolean
	 */
	public function isSuccessful()
	{

		return ($this->getErrorMessage() === '') ? true : false;

	}

	/**
	 * Remove the session from a cURL multi handle.
	 *
	 * @param  curl_multi $multiHandle
	 * @return int
	 */
	public function removeMultiHandle($multiHandle)
	{

		$this->isMulti = false;

		return curl_multi_remove_handle($multiHandle, $this->handle);

	}

}
