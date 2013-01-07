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

use jyggen\Curl\Session;

class Dispatcher
{

	protected $sessions = array();

	/**
	 * Add a new session to the dispatcher.
	 *
	 * @param	mixed	$session
	 * @return	jyggen\Curl\Dispatcher
	 */
	public function addSession($session)
	{

		// If $session is an array:
		if (is_array($session)) {
			foreach($session as $value) {

				// Call this method again.
				$this->addSession($value);

			}
		// If $session is an instance of jyggen\Curl\Session:
		} elseif ($session instanceof Session) {

			$this->sessions[] = $session;

		// Else throw an UnexpectedvalueException.
		} else {

			throw new \jyggen\UnexpectedValueException('Argument must be an instance or array with instances of jyggen\\Curl\\Session, "'.gettype($session).'" given.');

		}

		return $this;

	}

	/**
	 * Execute all added sessions.
	 *
	 * @param	int		$key
	 * @return	jyggen\Curl\Dispatcher
	 */
	public function execute($key = null)
	{

		// If a key is specified:
		if ($key !== null) {

			$this->executeSingle($key);

		// If there's only one session:
		} elseif (($no = count($this->sessions)) === 1) {

			$this->executeSingle();

		// If there's multiple sessions:
		} elseif ($no !== 0) {

			$this->executeMultiple();

		// Else throw an EmptyDispatcherException.
		} else throw new \jyggen\EmptyDispatcherException('You must add at least one session to the dispatcher to execute requests.');

		return $this;

	}

	protected function executeMultiple()
	{

		$mh = curl_multi_init();

		foreach ($this->sessions as $session) {

			curl_multi_add_handle($mh, $session->getHandle());

		}

		do {

			$mrc = curl_multi_exec($mh, $active);

		} while ($mrc === CURLM_CALL_MULTI_PERFORM);

		while ($active and $mrc === CURLM_OK) {

			// Workaround for PHP Bug #61141.
			if (curl_multi_select($mh) !== -1) { usleep(100); }

			do {

				$mrc = curl_multi_exec($mh, $active);

			} while ($mrc === CURLM_CALL_MULTI_PERFORM);

		}


		if ($mrc !== CURLM_OK) {

			throw new \jyggen\CurlErrorException('cURL read error #'.$mrc);

		}

		foreach ($this->sessions as $key => $session) {

			$handle = $session->getHandle();

			if (($error = curl_error($handle)) === '') {

				$session->setResponse(curl_multi_getcontent($handle));

			} else throw new \jyggen\CurlErrorException($error);

			curl_multi_remove_handle($mh, $handle);

		}

		curl_multi_close($mh);

	}

	/**
	 * Execute a single session.
	 *
	 * @param	int		$key
	 * @return	void
	 */
	protected function executeSingle($key = 0)
	{

		// If $key is a valid session:
		if (array_key_exists($key, $this->sessions)) {

			$session  = $this->sessions[$key];
			$handle   = $session->getHandle();
			$response = curl_exec($handle);

			// If $response isn't false:
			if($response !== false) {

				$session->setResponse($response);

			// Else throw a CurlErrorExcepetion.
			} else throw new \jyggen\CurlErrorException(curl_error($handle));

		// Else throw an InvalidKeyException.
		} else throw new \jyggen\InvalidKeyException('Session with key #'.$key.' does not exist.');

	}

	public function getResponses()
	{

		$responses = array();

		foreach($this->sessions as $session) {

			$responses[] = $session->getResponse();

		}

		return $responses;

	}

	public function getSessions()
	{

		return $this->sessions;

	}

}
