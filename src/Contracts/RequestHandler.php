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
 * @version    1.0.0
 * @author     Grégory Saive <greg@evias.be>
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/php-nem-laravel
 */
namespace NEM\Contracts;

/**
 * This is the RequestHandler interface
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
interface RequestHandler
    extends Connector
{
    /**
     * This method should implement features for sending
     * GET requests with the implemented library.
     *
     * @param  string $uri
     * @param  string $bodyJSON
     * @param  array  $options
     * @param  boolean  $synchronous
     * @return [type]          [description]
     */
    public function get($uri, $bodyJSON, array $options = [], $synchronous = false);

    /**
     * This method should implement features for sending
     * POST requests with the implemented library.
     *
     * @param  string $uri
     * @param  string $bodyJSON
     * @param  array  $options
     * @param  boolean  $synchronous
     * @return [type]          [description]
     */
    public function post($uri, $bodyJSON, array $options = [], $synchronous = false);
}
