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

use Jyggen\Curl\RequestInterface;

/**
 * Response
 *
 * Represents an HTTP response.
 */
class Response extends \Symfony\Component\HttpFoundation\Response
{
    /**
     * Forge a new object based on a request.
     * @param  RequestInterface $request
     * @return Response
     */
    public static function forge(RequestInterface $request)
    {
        $headerSize = $request->getInfo(CURLINFO_HEADER_SIZE);
        $response   = $request->getRawResponse();
        $content    = (strlen($response) === $headerSize) ? '' : substr($response, $headerSize);
        $rawHeaders = rtrim(substr($response, 0, $headerSize));
        $headers    = [];

        foreach (preg_split('/(\\r?\\n)/', $rawHeaders) as $header) {
            if ($header) {
                $headers[] = $header;
            } else {
                $headers = [];
            }
        }

        $headerBag = [];
        $info      = $request->getInfo();
        $status    = explode(' ', $headers[0]);
        $status    = explode('/', $status[0]);

        unset($headers[0]);

        foreach ($headers as $header) {
            list($key, $value)     = explode(': ', $header);
            $headerBag[trim($key)] = trim($value);
        }

        $response = new static($content, $info['http_code'], $headerBag);
        $response->setProtocolVersion($status[1]);
        $response->setCharset(substr(strstr($response->headers->get('Content-Type'), '='), 1));

        return $response;
    }
}
