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

use jyggen\Curl\RequestInterface;

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
        $content    = substr($response, $headerSize);
        $rawHeaders = rtrim(substr($response, 0, $headerSize));
        $headers    = array();

        foreach (preg_split('/(\\r?\\n)/', $rawHeaders) as $header) {
            if ($header) {
                $headers[] = $header;
            } else {
                $headers = array();
            }
        }

        $headerBag = array();
        $info      = $request->getInfo();
        $status    = explode(' ', $headers[0]);

        list($protocol, $version) = explode('/', $status[0]);
        unset($headers[0]);

        foreach ($headers as $header) {

            list($key, $value)     = explode(': ', $header);
            $headerBag[trim($key)] = trim($value);

        }

        $response = new static($content, $info['http_code'], $headerBag);
        $response->setProtocolVersion($version);
        $response->setCharset(substr(strstr($response->headers->get('Content-Type'), '='), 1));

        return $response;

    }
}
