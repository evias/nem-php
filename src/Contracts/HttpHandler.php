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
namespace evias\NEMBlockchain\Contracts;

/**
 * This is the HttpHandler interface
 *
 * This interface defines a Contract for HTTP Handlers
 * implementations. This allows extending the feature to
 * different libraries than the one provided by default.
 *
 * The implemented NEM blockchain API calls require only
 * GET and POST requests as of the first version.
 * Further methods could be added to this contract in
 * case implemented NEM API calls need those.
 *
 * @author Grégory Saive <greg@evias.be>
 */
interface HttpHandler
    extends Connector
{
    /**
     * This method should implement features for sending
     * GET requests with the implemented library.
     *
     * @param  string $uri     [description]
     * @param  array  $params  [description]
     * @param  array  $headers [description]
     * @return [type]          [description]
     */
    public function get($uri, array $params = [], array $headers = []);

    /**
     * This method should implement features for sending
     * POST requests with the implemented library.
     *
     * @param  string $uri     [description]
     * @param  array  $params  [description]
     * @param  array  $headers [description]
     * @return [type]          [description]
     */
    public function post($uri, array $params = [], array $headers = []);
}
