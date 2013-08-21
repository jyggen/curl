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

namespace jyggen\Curl;

interface RequestInterface
{
    /**
     * Add the request to a cURL multi handle.
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
     * Remove the request from a cURL multi handle.
     *
     * @param  curl_multi $multiHandle
     * @return int
     */
    public function removeMultiHandle($multiHandle);
}
