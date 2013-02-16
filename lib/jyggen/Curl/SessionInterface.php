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

interface SessionInterface
{

	/**
	 * Retrieve the latest error.
	 *
	 * @return string
	 */
	public function getErrorMessage();

	/**
	 * Retrieve the cURL handle.
	 *
	 * @return curl
	 */
	public function getHandle();

	/**
	 * Get information regarding the session.
	 *
	 * @param  int $key null
	 * @return mixed
	 */
	public function getInfo($key = null);

	/**
	 * Get this session's response.
	 *
	 * @return array
	 */
	public function getResponse();

	/**
	 * Set an option for the session.
	 *
	 * @param  mixed $option
	 * @param  mixed $value  null
	 * @return jyggen\Curl\SessionInterface
	 */
	public function setOption($option, $value = null);

	/**
	 * Add the session to a cURL multi handle.
	 *
	 * @param  curl_multi $multiHandle
	 * @return int
	 */
	public function addMultiHandle($multiHandle);

	/**
	 * Execute the request.
	 *
	 * @return void
	 */
	public function execute();

	/**
	 * If the request was successful or not.
	 *
	 * @return boolean
	 */
	public function isSuccessful();

	/**
	 * Remove the session from a cURL multi handle.
	 *
	 * @param  curl_multi $multiHandle
	 * @return int
	 */
	public function removeMultiHandle($multiHandle);

}
