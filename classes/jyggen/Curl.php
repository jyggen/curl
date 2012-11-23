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

class Curl
{

	protected static $_instance  = null;
	protected static $_instances = array();

	public static function delete()
	{

	}

	public static function get($urls)
	{

		$request  = static::forge();
		$sessions = $request->addUrl($urls);

		if(is_array($sessions)) {
			foreach($sessions as $session) {
				$session->setOption(CURLOPT_RETURNTRANSFER, true);
			}
		} else {
			$sessions->setOption(CURLOPT_RETURNTRANSFER, true);
		}

		$request->execute();

		return $request->getResponse();

	}

	public static function post()
	{

	}

	/**
	 * Forge a Request object
	 *
	 * @param string  $name    instance name
	 * @return object Request
	 */
	public static function forge($name = null)
	{

		// If no name is supplied, generate one using uniqid().
		$name or $name = uniqid();

		// Check if there's already an instance with that name.
		if (isset(static::$_instances[$name])) {

			throw new \Exception('You can not instantiate two requests using the same name "'.$name.'".');

		}

		// Create a Request object, store it and return it.
		$instance                  = new Curl\Request();
		static::$_instances[$name] = $instance;

		return $instance;

	}

	public static function instance($name = null)
	{

		if ($name !== null) {

			if (!array_key_exists($name, static::$_instances)) {

				return false;

			}

			return static::$_instances[$name];

		}

		if (static::$_instance === null) {

			static::$_instance = static::forge();

		}

		return static::$_instance;

	}


}