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
 * This is the Connector interface
 *
 * This interface defines a Contract for generic
 * Connector objects.
 *
 * A "Connector" object must define the properties
 * `use_ssl`, `host`, `port` and `endpoint`.
 *
 * @see  \evias\NEMBlockchain\Contracts\ConnectionPolicy
 * @author Grégory Saive <greg@evias.be>
 */
interface Connector
{
    /**
     * Setter for `use_ssl` property.
     *
     * @param  string $host
     * @return \evias\NEMBlockchain\API
     */
    public function setUseSsl($use_ssl);

    /**
     * Getter for the `use_ssl` property.
     *
     * @return string
     */
    public function getUseSsl();

    /**
     * Setter for `host` property.
     *
     * @param  string $host
     * @return \evias\NEMBlockchain\API
     */
    public function setHost($host);

    /**
     * Getter for the `host` property.
     *
     * @return string
     */
    public function getHost();

    /**
     * Setter for `port` property.
     *
     * @param  integer $port
     * @return \evias\NEMBlockchain\API
     */
    public function setPort($port);

    /**
     * Getter for the `port` property.
     *
     * @return string
     */
    public function getPort();

    /**
     * Setter for `endpoint` property.
     *
     * @param  string $endpoint
     * @return \evias\NEMBlockchain\API
     */
    public function setEndpoint($endpoint);

    /**
     * Getter for the `endpoint` property.
     *
     * @return string
     */
    public function getEndpoint();
}