<?php
/**
 * A simple and lightweight cURL library with support for multiple requests in parallel.
 *
 * @package     Curl
 * @version     3.0.1
 * @author      Jonas Stendahl
 * @license     MIT License
 * @copyright   2013 Jonas Stendahl
 * @link        http://github.com/jyggen/curl
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
