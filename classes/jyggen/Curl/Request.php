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

class Request
{

	protected $sessions = array();
	protected $response = null;
	protected $retry    = 0;

	public function addUrl($url)
	{

		if (is_array($url)) {

			$sessions = array();
			foreach ($url as $u) {

				$sessions[] = $this->addUrl($u);

			}

			return $sessions;

		} else {

			$session          = new Session($url);
			$this->sessions[] = $session;

			return $session;

		}

	}

	public function execute($key = false)
	{

		$no = count($this->sessions);

		if ($no == 1) {

			$response = $this->executeSingle();

		} elseif ($no > 1) {

			$response = ($key === false) ? $this->executeMultiple() : $this->executeSingle($key);

		}

		$this->response = Response::forge($response);

		return $this->response;

	}

	protected function executeSingle($key = 0)
	{

		$session = $this->sessions[$key];

		if ($this->retry > 0 ) {

			$code = 0;
			while ($retry >= 0 && ($code == 0 || $code >= 400)) {

				$res  = $session->execute();
				$code = $session->getInfo(CURLINFO_HTTP_CODE);

				$retry--;

			}

		} else {

			$res = $session->execute();

		}

		return array(
			'content' => $res,
			'info'    => $session->getInfo()
		);

	}

	protected function executeMultiple()
	{

		$mh = curl_multi_init();

		foreach ($this->sessions as $session) {

			curl_multi_add_handle($mh, $session->handle);

		}

		do {

			$mrc = curl_multi_exec($mh, $active);

		} while ($mrc == CURLM_CALL_MULTI_PERFORM);

		while ($active && $mrc == CURLM_OK) {

			if (curl_multi_select($mh) != -1) {

				do {

					$mrc = curl_multi_exec($mh, $active);

				} while ($mrc == CURLM_CALL_MULTI_PERFORM);

			}

		}

		if ($mrc != CURLM_OK) {

			throw new \RuntimeException('cURL read error #'.$mrc);

		}

		foreach ($this->sessions as $key => $session) {

			$code = $session->info(CURLINFO_HTTP_CODE);

			if ($code[0] > 0 && $code[0] < 400) {

				$res[] = curl_multi_getcontent($session->handle);

			} else {

				if ($this->retry > 0) {

					$retry        = $this->retry;
					$this->retry -= 1;
					$eRes         = $this->execSingle($key);
					$res[]        = ($eRes === true) ? $eRes : false;
					$this->retry  = $retry;

				} else {

					$res[] = false;

				}

			}

			curl_multi_remove_handle($mh, $session->handle);

		}

		curl_multi_close($mh);

		return $res;

	}

	public function getResponse()
	{

		if(!$this->response instanceof Response) {
			throw new \RuntimeException('You must execute your request before you can retrieve the response.');
		}

		return $this->response;

	}

}