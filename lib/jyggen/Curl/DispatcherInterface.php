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

interface DispatcherInterface
{

	/**
	 * Add a Session.
	 *
	 * @param  jyggen\Curl\SessionInterface $session
	 * @return void
	 */
	public function add(SessionInterface $session);

	/**
	 * Remove all sessions.
	 *
	 * @return void
	 */
	public function clear();

	/**
	 * Execute all added sessions.
	 *
	 * @return void
	 */
	 public function execute();

	/**
	 * Retrieve all or a specific session.
	 *
	 * @param  int $key null
	 * @return mixed
	 */
	public function get($key = null);

	/**
	 * Remove a specific session.
	 *
	 * @param  int $key
	 * @return jyggen\Curl\Dispatcher
	 */
	public function remove($key);

}
