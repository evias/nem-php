<?php
/**
 * Part of the evias/php-nem-laravel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under MIT License.
 *
 * This source file is subject to the MIT License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    evias/php-nem-laravel
 * @version    0.0.2
 * @author     Grégory Saive <greg@evias.be>
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/php-nem-laravel
 */
namespace evias\NEMBlockchain\Handlers;

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

/**
 * This is the GuzzleHttpHandler class
 *
 * @author Grégory Saive <greg@evias.be>
 */
class GuzzleHttpHandler
    extends AbstractHttpHandler
{
    /**
     * [normalizeHeaders description]
     * @param  array  $headers [description]
     * @return [type]          [description]
     */
    protected function normalizeHeaders(array $headers)
    {
        if (empty($headers["User-Agent"]))
            $headers["User-Agent"] = "evias NEM Wrapper";

        if (empty($headers["Accept"]))
            $headers["Accept"] = "application/json";

        if (empty($headers["Content-Type"]))
            $headers["Content-Type"] = "application/json";

        return $headers;
    }

    /**
     * This method triggers a GET request to the given
     * URI using the GuzzleHttp client.
     *
     * @see  \evias\NEMBlockchain\Contracts\HttpHandler
     * @param  string $uri
     * @param  string $bodyJSON
     * @param  array  $options
     * @param  boolean  $synchronous
     * @return [type]
     */
    public function get($uri, $bodyJSON, array $options = [], $synchronous = false)
    {
        $headers = [];
        if (!empty($options["headers"]))
            $headers = $options["headers"];

        // overwrite mandatory headers
        $headers["Content-Length"] = strlen($bodyJSON);
        $headers = $this->normalizeHeaders($headers);

        // prepare guzzle request options
        $options = array_merge($options, [
            "body"    => $bodyJSON,
            "headers" => $headers,
        ]);

        $client  = new Client(["base_uri" => $this->getBaseUrl()]);
        $request = new Request("GET", $uri, $options);

        if ($synchronous)
            return $client->send($request);

        $promise = $client->sendAsync($request);
        $promise->then(
            function(ResponseInterface $response)
            {
                return $response;
            },
            function(RequestException $exception)
            {
                //XXX return created response with error code, etc.
                return $exception->getMessage();
            }
        );
        return $promise;
    }

    /**
     * This method triggers a POST request to the given
     * URI using the GuzzleHttp client.
     *
     * @see  \evias\NEMBlockchain\Contracts\HttpHandler
     * @param  string $uri
     * @param  string $bodyJSON
     * @param  array  $options
     * @param  boolean  $synchronous
     * @return [type]
     */
    public function post($uri, $bodyJSON, array $options = [], $synchronous = false)
    {
    }
}
