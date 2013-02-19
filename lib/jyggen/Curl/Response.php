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

class Response extends \Symfony\Component\HttpFoundation\Response
{

	public static function forge(SessionInterface $session)
	{

        list($headers, $content) = explode("\r\n\r\n", $session->getRawResponse());

        $headers   = explode("\r\n", $headers);
        $headerBag = array();
        $info      = $session->getInfo();
        $status    = explode(' ', $headers[0]);

        list($protocol, $version) = explode('/', $status[0]);

        unset($headers[0]);

        foreach ($headers as $header) {

            $header = explode(': ', $header);

            $headerBag[trim($header[0])] = trim($header[1]);

        }

        $response = new Response($content, $info['http_code'], $headerBag);
        $response->setProtocolVersion($version);

        return $response;

	}

}
