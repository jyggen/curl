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

use jyggen\Curl\SessionInterface;

class Dispatcher implements DispatcherInterface
{

	/**
	 * The cURL multi handle.
	 *
	 * @var curl_multi
	 */
	protected $handle;

	/**
	 * All of the added sessions.
	 *
	 * @var array
	 */
	protected $sessions = array();

	/**
	 * Create a new Dispatcher instance.
	 *
	 * @return void
	 */
	public function __construct()
	{

		$this->handle = curl_multi_init();

	}

	/**
	 * Shutdown sequence.
	 *
	 * @return void
	 */
	public function __destruct()
	{

		curl_multi_close($this->handle);

	}

	/**
	 * Add a Session.
	 *
	 * @param  jyggen\Curl\SessionInterface $session
	 * @return void
	 */
	public function add(SessionInterface $session)
	{

		// Tell the session to use our handle.
		$session->addMultiHandle($this->handle);

		// Store the session.
		$this->sessions[] = $session;

		return (count($this->sessions) - 1);

	}

	/**
	 * Remove all sessions.
	 *
	 * @return void
	 */
	public function clear()
	{

		// Loop through all sessions and remove
		// their relationship to our handle.
		foreach ($this->sessions as $session) {

			$session->removeMultiHandle($this->handle);

		}

		// Reset the sessions array.
		$this->sessions = array();

	}

	/**
	 * Execute all added sessions.
	 *
	 * @return void
	 */
	public function execute()
	{

		do {

			$mrc = curl_multi_exec($this->handle, $active);

		} while ($mrc === CURLM_CALL_MULTI_PERFORM);

		while ($active and $mrc === CURLM_OK) {

			// Workaround for PHP Bug #61141.
			if (curl_multi_select($this->handle) !== -1) { usleep(100); }

			do {

				$mrc = curl_multi_exec($this->handle, $active);

			} while ($mrc === CURLM_CALL_MULTI_PERFORM);

		}


		if ($mrc !== CURLM_OK) {

			throw new \jyggen\CurlErrorException('cURL read error #'.$mrc);

		}

		foreach ($this->sessions as $key => $session) {

			$session->execute();
			$session->removeMultiHandle($this->handle);

		}

	}

	/**
	 * Retrieve all or a specific session.
	 *
	 * @param  int $key null
	 * @return mixed
	 */
	public function get($key = null)
	{

		// Return all sessions.
		if (is_null($key)) {

			return $this->sessions;

		// Return a specific session if it exists.
		} elseif (array_key_exists($key, $this->sessions)) {

			return $this->sessions[$key];

		// Return false.
		} else {

			return false;

		}

	}

	/**
	 * Remove a specific session.
	 *
	 * @param  int $key
	 * @return jyggen\Curl\Dispatcher
	 */
	public function remove($key)
	{

		// Make sure the session exists before we try to remove it.
		if (array_key_exists($key, $this->sessions)) {

			$this->sessions[$key]->removeMultiHandle($this->handle);
			unset($this->sessions[$key]);

		}

	}

}
