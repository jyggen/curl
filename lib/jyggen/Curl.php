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

namespace jyggen;

class Curl
{

	protected static $dispatcher = 'jyggen\\Curl\\Dispatcher';
	protected static $session    = 'jyggen\\Curl\\Session';

	/**
	 * Static helper to do DELETE requests.
	 *
	 * @param  mixed $url
	 * @return array
	 */
	public static function delete($url)
	{

		if (!is_array($url)) {

			$urls = array($url => null);

		} else {

			foreach ($url as $value) {

				$urls[$value] = null;

			}

		}

		return static::makeRequest('DELETE', $urls);

	}

	/**
	 * Static helper to do GET requests.
	 *
	 * @param  mixed $url
	 * @return array
	 */
	public static function get($url)
	{

		if (!is_array($url)) {

			$urls = array($url => null);

		} else {

			foreach ($url as $value) {

				$urls[$value] = null;

			}

		}

		return static::makeRequest('GET', $urls);

	}

	/**
	 * Static helper to do POST requests.
	 *
	 * @param  mixed $url
	 * @param  array $data
	 * @return array
	 */
	public static function post($urls, array $data = null)
	{

		if (!is_array($urls)) {

			$urls = array($urls => $data);

		}

		return static::makeRequest('POST', $urls);

	}

	/**
	 * Static helper to do PUT requests.
	 *
	 * @param  mixed $urls
	 * @param  array $data
	 * @return array
	 */
	public static function put($urls, array $data = null)
	{

		if (!is_array($urls)) {

			$urls = array($urls => $data);

		}

		return static::makeRequest('PUT', $urls);

	}

	/**
	 * Get the dispatcher class used by the helper.
	 * @return  jyggen\Curl\DispatcherInterface
	 */
	public static function getDispatcher()
	{

		return static::$dispatcher;

	}


	/**
	 * Get the session class used by the helper.
	 * @return  jyggen\Curl\SessionInterface
	 */
	public static function getSession()
	{

		return static::$session;

	}

	/**
	 * Set the dispatcher class used by the helper.
	 * @param  string $classname
	 * @return null
	 */
	public static function setDispatcher($classname)
	{

		$implements = class_implements($classname);

		if ($implements !== false and in_array('jyggen\\Curl\\DispatcherInterface', $implements)) {

			static::$dispatcher = $classname;

		} else throw new UnexpectedValueException(sprintf('Dispatcher "%s" must implement "jyggen\\Curl\\DispatcherInterface"', $classname));


	}

	/**
	 * Set the session class used by the helper.
	 * @param  string $classname
	 * @return null
	 */
	public static function setSession($classname)
	{

		$implements = class_implements($classname);

		if ($implements !== false and in_array('jyggen\\Curl\\SessionInterface', $implements)) {

			static::$dispatcher = $classname;

		} else throw new UnexpectedValueException(sprintf('Session "%s" must implement "jyggen\\Curl\\SessionInterface"', $classname));

	}

	/**
	 * Setup and execute a HTTP request.
	 *
	 * @param  string $method
	 * @param  array  $urls
	 * @return array
	 */
	protected static function makeRequest($method, $urls)
	{

		// Create a new Dispatcher.
		$dispatcher = new static::$dispatcher();

		// Foreach $urls:
		foreach ($urls as $url => $data) {

			if($data !== null) {

				$data = http_build_query($data);

			}

			// Create a new Session.
			$session = new static::$session($url);

			// Follow any 3xx HTTP status code.
			$session->setOption(CURLOPT_FOLLOWLOCATION, true);

			if ($method === 'DELETE') {

				// Set request method to DELETE.
				$session->setOption(CURLOPT_CUSTOMREQUEST, 'DELETE');

			} elseif ($method === 'POST') {

				// Add the POST data to the session.
				$session->setOption(CURLOPT_POST, true);
				$session->setOption(CURLOPT_POSTFIELDS, $data);

			} elseif ($method === 'PUT') {

				// Write the PUT data to memory.
				$fh = fopen('php://memory', 'rw');
				fwrite($fh, $data);
				rewind($fh);

				// Add the PUT data to the session.
				$session->setOption(CURLOPT_INFILE, $fh);
				$session->setOption(CURLOPT_INFILESIZE, mb_strlen($data, 'UTF-8'));
				$session->setOption(CURLOPT_PUT, true);

			} else {

				// Redundant, but reset the method to GET.
				$session->setOption(CURLOPT_HTTPGET, true);

			}

			// Add the session to the dispatcher.
			$dispatcher->add($session);

		}

		// Execute the request(s).
		$dispatcher->execute();

		$sessions  = $dispatcher->get();
		$responses = array();

		foreach ($sessions as $session) {

			$responses[] = $session->getResponse();

		}

		return (count($urls) === 1) ? $responses[0] : $responses;

	}

}