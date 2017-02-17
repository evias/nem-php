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

use GuzzleHttp\Client;
use evias\NEMBlockchain\Contracts\RequestHandler;
use evias\NEMBlockchain\Traits\Connectable;

/**
 * This is the AbstractRequestHandler abstract class
 *
 * This class should be extended by RequestHandler
 * specialization classes.
 *
 * @author Grégory Saive <greg@evias.be>
 */
abstract class AbstractRequestHandler
    implements RequestHandler
{
    use Connectable;

    /**
     * This method makes sure mandatory headers are
     * added in case they are not present.
     *
     * @param  array  $headers [description]
     * @return [type]          [description]
     */
    protected function normalizeHeaders(array $headers)
    {
        if (empty($headers["User-Agent"]))
            $headers["User-Agent"] = "evias NEM Blockchain Wrapper";

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
     * @see  \evias\NEMBlockchain\Contracts\RequestHandler
     * @param  string $uri
     * @param  string $bodyJSON
     * @param  array  $options
     * @param  boolean  $synchronous
     * @return [type]          [description]
     */
    abstract public function get($uri, $bodyJSON, array $options = [], $synchronous = false);

    /**
     * This method triggers a POST request to the given
     * URI using the GuzzleHttp client.
     *
     * @see  \evias\NEMBlockchain\Contracts\RequestHandler
     * @param  string $uri
     * @param  string $bodyJSON
     * @param  array  $options
     * @param  boolean  $synchronous
     * @return [type]          [description]
     */
    abstract public function post($uri, $bodyJSON, array $options = [], $synchronous = false);
}
