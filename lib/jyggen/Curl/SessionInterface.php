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

interface SessionInterface {

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
	 * Remove the session from a cURL multi handle.
	 *
	 * @param  curl_multi $multiHandle
	 * @return int
	 */
	public function removeMultiHandle($multiHandle);

}