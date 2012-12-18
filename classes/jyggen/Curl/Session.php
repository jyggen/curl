<?php
/**
 * A lightweight cURL library with support for multiple requests in parallel.
 *
 * @package Curl
 * @version 1.0
 * @author Jonas Stendahl
 * @license MIT License
 * @copyright 2012 Jonas Stendahl
 * @link http://github.com/jyggen/curl
 */

namespace jyggen\Curl;

class Session
{

	protected $handle, $response;

	protected $defaults = array(
		CURLOPT_RETURNTRANSFER => true,
	);

	/**
	 * Create a new Session.
	 *
	 * @param	string	$url
	 * @return	void
	 */
	public function __construct($url)
	{

		$this->handle = curl_init($url);

		curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);

	}

	/**
	 * Retrieve the session's cURL handle.
	 *
	 * @return	resource
	 */
	public function getHandle()
	{

		return $this->handle;

	}

	/**
	 * Get information regarding the session.
	 *
	 * @param	int		$key
	 * @return	string
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
	 * @return	array
	 */
	public function getResponse()
	{

		return $this->response;

	}

	/**
	 * Set an option for the session.
	 *
	 * @param	int		$option
	 * @param	mixed	$value
	 * @return	bool
	 */
	public function setOption($option, $value = null)
	{

		// If $option is an array:
		if (is_array($option)) {

			foreach ($option as $opt => $val) {

				return $this->setOption($this->handle, $opt, $val);

			}

		// Else if $option isn't a required default value:
		} elseif (!array_key_exists($option, $this->defaults)) {

			return curl_setopt($this->handle, $option, $value);

		// Else throw a ProtectedOPtionException.
		} else throw new \jyggen\ProtectedOptionException('To prevent unexpected behavior you are not allowed to change option '.$option.'.');

	}

	public function setResponse($response)
	{

		if ($response === true) {

			$this->response = array(
				'info' => $this->getInfo(),
			);

		} else {

			$this->response = array(
				'data' => $response,
				'info' => $this->getInfo(),
			);

		}

	}

}
