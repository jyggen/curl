<?php
/**
 * This file is part of the jyggen/curl library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Jonas Stendahl <jonas.stendahl@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @link https://jyggen.com/projects/jyggen-curl Documentation
 * @link https://packagist.org/packages/jyggen/curl Packagist
 * @link https://github.com/jyggen/curl GitHub
 */

namespace Jyggen\Curl;

/**
 * Sends HTTP requests.
 */
interface DispatcherInterface
{
    /**
     * Adds a request to the stack.
     *
     * @param RequestInterface $request
     * @return integer
     */
    public function add(RequestInterface $request);

    /**
     * Retrieves all requests from the stack.
     *
     * @return array
     */
    public function all();

    /**
     * Removes all requests from the stack.
     *
     * @return void
     */
    public function clear();

    /**
     * Executes all requests in the stack.
     *
     * @param callable $callback
     * @return void
     */
    public function execute(callable $callback = null);

    /**
     * Retrieves a specific request from the stack.
     *
     * @param integer $key
     * @return RequestInterface|null
     */
    public function get($key);

    /**
     * Removes a specific request from the stack.
     *
     * @param integer $key
     */
    public function remove($key);
}
