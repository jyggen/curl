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

namespace jyggen;

use jyggen\Curl\Dispatcher;
use jyggen\Curl\Session;

class Curl
{

	/**
	 * Static helper to retrieve URLs.
	 *
	 * @param	mixed	$url
	 * @return	array
	 */
	public static function get($urls)
	{

		// Create a new Dispatcher.
		$dispatcher = new Dispatcher;
		$multiple   = true;

		// Turn $urls into an array if it isn't.
		if(!is_array($urls)) {

			$urls     = array($urls);
			$multiple = false;

		}

		// Foreach $urls:
		foreach ($urls as $url) {

			// Create a new Session.
			$session = new Session($url);

			// Follow any 3xx HTTP status code.
			$session->setOption(CURLOPT_FOLLOWLOCATION, true);

			// Add the session to the dispatcher.
			$dispatcher->addSession($session);

		}

		// Execute the request(s).
		$responses = $dispatcher->execute()->getResponses();

		if($multiple === true) {

			return $responses;

		} else {

			return $responses[0];

		}

	}

}
