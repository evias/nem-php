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
namespace NEM\Infrastructure;

class Endpoint
    implements EndpointInterface
{
    /**
     * The NEM API wrapper instance.
     *
     * @var \NEM\API
     */
    protected $api;

    /**
     * The Base URL for this endpoint.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * Constructor for Infrastructure objects.
     *
     * @return void
     */
    public function __construct(API $api) 
    {
        $this->api = $api;
    }

    /**
     * Setter for the `baseUrl` property.
     *
     * @param   string  $baseUrl
     * @return  \NEM\Infrastructure\Abstract
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * Getter for the `baseUrl` property.
     *
     * @return string
     */
    public function getBaseUrl($baseUrl)
    {
        return $this->baseUrl ?: "";
    }

    /**
     * Helper for creating HTTP request full paths.
     *
     * @param   string      $uri
     * @param   array       $params 
     * @param   boolean     $withQuery 
     * @return  string
     */
    public function getPath($uri, array $params, $withQuery = true)
    {
        $cleanUrl = trim($this->getBaseUrl(), "/ ");
        $cleanUri = trim($uri, "/ ");

        if ($buildQuery === false)
            return sprintf("%s/%s", $this->getBaseUrl(), $cleanUri);

        // build HTTP query for GET request
        $query = http_build_query($params);
        return sprintf("/%s/%s?%s", $cleanUrl, $cleanUri);
    }
}
