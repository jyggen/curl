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

use Closure;
use Jyggen\Curl\DispatcherInterface;
use Jyggen\Curl\Exception\CurlErrorException;
use Jyggen\Curl\Exception\InvalidArgumentException;

/**
 * Sends HTTP requests asynchronously.
 */
class Dispatcher implements DispatcherInterface
{
    /**
     * The cURL multi handle.
     *
     * @var resource
     */
    protected $handle;

    /**
     * All added requests.
     *
     * @var array
     */
    protected $requests = [];

    /**
     * The size of each request stack.
     *
     * @var integer
     */
    protected $stackSize = 42;

    /**
     * Constructs a `Dispatcher` instance.
     */
    public function __construct()
    {
        $this->handle = curl_multi_init();
    }

    /**
     * {@inheritdoc}
     */
    public function add(RequestInterface $request)
    {
        $this->requests[] = $request;
        return (count($this->requests) - 1);
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->requests;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->requests = [];
    }

    /**
     * {@inheritdoc}
     */
    public function execute(callable $callback = null)
    {
        $stacks = $this->buildStacks();

        foreach ($stacks as $requests) {
            // Tell each request to use this dispatcher.
            foreach ($requests as $request) {
                $status = curl_multi_add_handle($this->handle, $request->getHandle());
                if ($status !== CURLM_OK) {
                    throw new CurlErrorException(sprintf(
                        'Unable to add request to cURL multi handle (code #%u)',
                        $status
                    ));
                }
            }

            // Start dispatching the requests.
            $this->dispatch();

            // Loop through all requests and remove their relationship to our dispatcher.
            foreach ($requests as $request) {
                if ($request->isSuccessful() === false) {
                    throw new CurlErrorException($request->getErrorMessage());
                }

                $request->setRawResponse(curl_multi_getcontent($request->getHandle()));
                curl_multi_remove_handle($this->handle, $request->getHandle());

                if ($callback !== null) {
                    $callback($request->getResponse());
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        // Otherwise, if the key exists; return that request, else return null.
        return (isset($this->requests[$key])) ? $this->requests[$key] : null;
    }

    /**
     * Retrieves the maximum stack size.
     *
     * @return integer
     */
    public function getStackSize()
    {
        return $this->stackSize;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        // Make sure the request exists before we try to remove it.
        if (array_key_exists($key, $this->requests)) {
            $this->requests[$key]->removeMultiHandle($this->handle);
            unset($this->requests[$key]);
        }
    }

    /**
     * Sets the maximum stack size.
     *
     * @param integer $size
     */
    public function setStackSize($size)
    {
        if (gettype($size) !== 'integer') {
            throw new InvalidArgumentException('setStackSize() expected an integer, '.gettype($size).' received.');
        }

        $this->stackSize = $size;
    }

    /**
     * Builds stacks of requests.
     *
     * @return array
     */
    protected function buildStacks()
    {
        $stacks   = [];
        $stackNo  = 0;
        $currSize = 0;

        foreach ($this->requests as $request) {
            if ($currSize === $this->stackSize) {
                $currSize = 0;
                $stackNo++;
            }

            $stacks[$stackNo][] = $request;
            $currSize++;
        }

        return $stacks;
    }

    /**
     * Dispatches all requests in the stack.
     */
    protected function dispatch()
    {
        // Start processing the requests.
        list($mrc, $active) = $this->process();

        // Keep processing requests until we're done.
        while ($active and $mrc === CURLM_OK) {
            // Process the next request.
            list($mrc, $active) = $this->process();
        }

        // Throw an exception if something went wrong.
        if ($mrc !== CURLM_OK) {
            throw new CurlErrorException('cURL read error #'.$mrc);
        }
    }

    /**
     * Processes all requests.
     *
     * @return array
     */
    protected function process()
    {
        // Workaround for PHP Bug #61141.
        if (curl_multi_select($this->handle) === -1) {
            usleep(100);
        }

        do {
            $mrc = curl_multi_exec($this->handle, $active);
        } while ($mrc === CURLM_CALL_MULTI_PERFORM);

        return [$mrc, $active];
    }
}
