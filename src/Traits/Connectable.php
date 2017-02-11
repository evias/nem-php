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
namespace evias\NEMBlockchain\Traits;

/**
 * This is the Connectable interface
 *
 * This trait defines methods for connection policy
 * management.
 *
 * Classes using this trait are considered as
 * Connectable with any kind of Protocol.
 * (http, https, ws, wss, ..)
 *
 * @author Grégory Saive <greg@evias.be>
 */
trait Connectable
{
    /**
     * Whether the current Connectable uses SSL encryption or not.
     *
     * @var boolean
     */
    protected $use_ssl;

    /**
     * The current Connectable connection Protocol
     *
     * @var string  i.e.: "http", "ws", "https"
     */
    protected $protocol;

    /**
     * The current Connectable connection Hostname
     *
     * @var string  i.e.: "127.0.0.1", "example.com", "my.do.main.com"
     */
    protected $host;

    /**
     * The current Connectable connection Port
     *
     * @var integer
     */
    protected $port;

    /**
     * This contains the API endpoint that we will
     * send the query to on the configured host and port.
     *
     * @var string  i.e: /ncc/api, /your-api, etc.
     */
    protected $endpoint;

    /**
     * Setter for `use_ssl` property.
     *
     * @param  string $host
     * @return \evias\NEMBlockchain\API
     */
    public function setUseSsl($use_ssl)
    {
        $this->use_ssl = $use_ssl;
        return $this;
    }

    /**
     * Getter for the `use_ssl` property.
     *
     * @return string
     */
    public function getUseSsl()
    {
        return $this->use_ssl;
    }

    /**
     * This method should implement a protocol
     * setter which will be used to determine
     * which Protocol is used in the Base URL.
     *
     * @param  string $protocol
     * @return \evias\NEMBlockchain\Contracts\HttpHandler
     */
    public function setProtocol($protocol)
    {
        $lowerProtocol = strtolower($protocol);

        // handle special cases where protocol is not
        // formatted as intended yet.

        if ("websocket" == $lowerProtocol)
            $lowerProtocol = "ws";

        if (in_array($lowerProtocol, ["https", "wss"])) {
            // provided secure type of protocol
            $this->setUseSsl(true);
            $lowerProtocol = substr($lowerProtocol, 0, -1);
        }

        $this->protocol = $lowerProtocol;
        return $this;
    }

    /**
     * This method should implement a protocol
     * getter.
     *
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Setter for `host` property.
     *
     * @param  string $host
     * @return \evias\NEMBlockchain\API
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Getter for the `host` property.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Setter for `port` property.
     *
     * @param  integer $port
     * @return \evias\NEMBlockchain\API
     */
    public function setPort($port)
    {
        $this->port = (int) $port;
        return $this;
    }

    /**
     * Getter for the `port` property.
     *
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Setter for `endpoint` property.
     *
     * @param  string $endpoint
     * @return \evias\NEMBlockchain\API
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
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Getter for `base_url` property.
     *
     * @return string
     */
    public function getScheme()
    {
        $secure = ($this->getUseSsl() ? "s" : "");
        return sprintf("%s%s%s", $this->getProtocol(), $secure, "://");
    }

    /**
     * Getter for `base_url` property.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->getScheme() . $this->getHost() . $this->getEndpoint();
    }
}
