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

use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

/**
 * This is the GuzzleRequestHandler class
 *
 * This specialization uses the Guzzle
 * laravel wrapper to perform requests to
 * the configured API endpoints.
 *
 * @author Grégory Saive <greg@evias.be>
 */
class GuzzleRequestHandler
    extends AbstractRequestHandler
{
    /**
     * Use GuzzleHTTP Promises v6 Implementation to send
     * the request asynchronously. As mentioned in the source
     * code, this method will only leverage the advantages
     * of Asynchronous execution in later versions.
     *
     * The current version uses the Promises but will synchronously
     * execute the Request and wait for it to response.
     *
     * Configuring the onSuccess, onError and onReject callbacks
     * is possible using callables. Following signatures will
     * apply:
     *   - onSuccess: function(ResponseInterface $response)
     *   - onError: function(RequestException $exception)
     *   - onReject: function(string $reason)
     *
     * @param  Client  $client  [description]
     * @param  Request $request [description]
     * @param  array   $options [description]
     * @return [type]           [description]
     */
    protected function promiseResponse(Client $client, Request $request, array $options = [])
    {
        // Guzzle Promises do not allow Asynchronous Requests Handling,
        // I have implemented this feature only because it will
        // allow a better Response Time for Paralell Request Handling.
        // This will be implemented in later versions.
        // Because of this, the following snippet will basically work
        // just like a normal Synchronous request, except that the Success
        // and Error callbacks can be configured more conveniently.

        $successCallback = isset($options["onSuccess"]) && is_callable($options["onSuccess"]) ? $options["onSuccess"] : null;
        $errorCallback   = isset($options["onError"]) && is_callable($options["onError"]) ? $options["onError"] : null;
        $cancelCallback  = isset($options["onReject"]) && is_callable($options["onReject"]) ? $options["onReject"] : null;

        $promise = $client->sendAsync($request);
        $promise->then(
            function(ResponseInterface $response)
                use ($successCallback)
            {
                if ($successCallback)
                    return $successCallback($response);

                return $response;
            },
            function(RequestException $exception)
                use ($errorCallback)
            {
                if ($errorCallback)
                    return $errorCallback($exception);

                return $exception;
            }
        );

        if ($cancelCallback) {
            // register promise rejection callback (happens when the
            // cancel() method is called on promises.)
            $promise->otherwise($cancelCallback);
        }

        // Guzzle Promises advantages will only be leveraged
        // in Parelell request execution mode as all requests
        // will be sent in paralell and the handling time goes
        // down to the minimum response time of ALL promises.
        return $promise->wait();
    }

    /**
     * This method triggers a GET request to the given
     * URI using the GuzzleHttp client.
     *
     * Default behaviour disables Promises Features.
     *
     * Promises Features
     * - success callback can be configured with $options["onSuccess"],
     *   a ResponseInterface object will be passed to this callable when
     *   the Request Completes.
     * - error callback can be configured with $options["onError"],
     *   a RequestException object will be passed to this callable when
     *   the Request encounters an error
     *
     * @see  \evias\NEMBlockchain\Contracts\RequestHandler
     * @param  string $uri
     * @param  string $bodyJSON
     * @param  array  $options      can contain "headers" array, "onSuccess" callable,
     *                              "onError" callable and any other GuzzleHTTP request
     *                              options.
     * @param  boolean  $usePromises
     * @return [type]
     */
    public function get($uri, $bodyJSON, array $options = [], $usePromises = false)
    {
        $headers = [];
        if (!empty($options["headers"]))
            $headers = $options["headers"];

        // overwrite mandatory headers
	    if(!is_array($bodyJSON)){
		    $headers["Content-Length"] = strlen($bodyJSON);
	    }
        $headers = $this->normalizeHeaders($headers);

        // prepare guzzle request options
        $options = array_merge($options, [
            "body"    => $bodyJSON,
            "headers" => $headers,
        ]);

        $client  = new Client(["base_uri" => $this->getBaseUrl()]);
        $request = new Request("GET", $uri, $options);
        if (! $usePromises)
            // return the response object when the request is completed.
            // this behaviour handles the request synchronously.
            return $client->send($request);

        return $this->promiseResponse($client, $request, $options);
    }

    /**
     * This method triggers a POST request to the given
     * URI using the GuzzleHttp client.
     *
     * @see  \evias\NEMBlockchain\Contracts\RequestHandler
     * @param  string $uri
     * @param  string $bodyJSON
     * @param  array  $options
     * @param  boolean  $synchronous
     * @return [type]
     */
    public function post($uri, $bodyJSON, array $options = [], $usePromises = false)
    {
        $headers = [];
        if (!empty($options["headers"]))
            $headers = $options["headers"];

        // overwrite mandatory headers
	    if(!is_array($bodyJSON)){
		    $headers["Content-Length"] = strlen($bodyJSON);
	    }
        $headers = $this->normalizeHeaders($headers);

        // prepare guzzle request options

        $options = [
        	"headers" => $headers,
	        "json" => $bodyJSON,
        ];


        $client  = new Client(["base_uri" => $this->getBaseUrl()]);


		if (! $usePromises){
			$response = $client->request('POST', $uri, $options);
			return $response->getBody()->getContents();
		}

		//TODO: Implement post promise
	    throw new \Exception("Promises for POST is not implmented yet");

    }
}
