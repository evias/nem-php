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

use Unirest\Request;

/**
 * This is the UnirestHttpHandler class
 *
 * @author Grégory Saive <greg@evias.be>
 */
class UnirestHttpHandler
    extends AbstractHttpHandler
{
    /**
     * This method triggers a GET request to the given
     * URI using the Unirest Request class.
     *
     * @see  \evias\NEMBlockchain\Contracts\HttpHandler
     * @param  string $uri
     * @param  string $bodyJSON
     * @param  array  $options
     * @param  boolean  $synchronous
     * @return [type]          [description]
     */
    public function get($uri, $bodyJSON, array $options = [], $synchronous = false)
    {
    }

    /**
     * This method triggers a POST request to the given
     * URI using the Unirest Request class.
     *
     * @see  \evias\NEMBlockchain\Contracts\HttpHandler
     * @param  string $uri
     * @param  string $bodyJSON
     * @param  array  $options
     * @param  boolean  $synchronous
     * @return [type]          [description]
     */
    public function post($uri, $bodyJSON, array $options = [], $synchronous = false)
    {
    }
}
