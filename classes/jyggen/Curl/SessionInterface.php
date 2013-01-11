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
	 * @return string
	 */
	public function getErrorMessage();

	/**
	 * @param  curl_multi $multiHandle
	 * @return int
	 */
	public function addMultiHandle($multiHandle);

	/**
	 * @return void
	 */
	public function execute();

	/**
	 * @return bool
	 */
	public function isSuccessful();

	/**
	 * @param  curl_multi $multiHandle
	 * @return int
	 */
	public function removeMultiHandle($multiHandle);

}
