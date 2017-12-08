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

abstract class Abstract 
{
    /** 
     * The Laravel IoC Container
     *
     * @var \Illuminate\Container\Container
     */
    protected $app;

    /**
     * The Base URL for this endpoint.
     *
     * @var string
     */
    protected $endpoint;

    /**
     * Constructor for Infrastructure objects.
     *
     * @return void
     */
    public function __construct() 
    {
    }

    /**
     * Setter for the `endpoint` property.
     *
     * @param   string  $endpoint
     * @return  \NEM\Infrastructure\Abstract
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * Getter for the `endpoint` property.
     *
     * @return string
     */
    public function getEndpoint($endpoint)
    {
        return $this->endpoint ?: "";
    }
}
