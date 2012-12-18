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
	 * Static helper to do DELETE requests.
	 *
	 * @param	mixed	$url
	 * @return	array
	 */
	public static function delete($urls);

	/**
	 * Static helper to do GET requests.
	 *
	 * @param	mixed	$url
	 * @return	array
	 */
	public static function get($urls)
	{

		// Create a new Dispatcher.
		$dispatcher = new Dispatcher;

		// Turn $urls into an array if it isn't.
		if (!is_array($urls)) { $urls = array($urls); }

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

		// If more than one URL was requested:
		if(count($urls) > 1) {

			return $responses;

		// Else:
		} else {

			return $responses[0];

		}

	}

	/**
	 * Static helper to do POST requests.
	 *
	 * @param	mixed	$url
	 * @param	array	$data
	 * @return	array
	 */
	public static function post($urls, $data = null)
	{

		// Create a new Dispatcher.
		$dispatcher = new Dispatcher;

		// Turn $urls into an array if it isn't.
		if (!is_array($urls)) { $urls = array($urls => $data); }

		// Foreach $urls:
		foreach ($urls as $url => $data) {

			// Create a new Session.
			$session = new Session($url);

			// Follow any 3xx HTTP status code.
			$session->setOption(CURLOPT_FOLLOWLOCATION, true);

			// Add the POST data to the session.
			$session->setOption(CURLOPT_POST, true);
			$session->setOption(CURLOPT_POSTFIELDS, $data);

			// Add the session to the dispatcher.
			$dispatcher->addSession($session);

		}

		// Execute the request(s).
		$responses = $dispatcher->execute()->getResponses();

		// If more than one URL was requested:
		if(count($urls) > 1) {

			return $responses;

		// Else:
		} else {

			return $responses[0];

		}

	}

	/**
	 * Static helper to do PUT requests.
	 *
	 * @param	mixed	$url
	 * @return	array
	 */
	public static function put($urls, $data = null);

	/*
	 * @todo refactor Curl::get() and Curl::post() to utilize
	 *       this method to avoid code repetition.
	 */
	protected static function makeRequest($method, $urls);

}
