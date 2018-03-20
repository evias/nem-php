<?php
/**
 * Part of the evias/nem-php package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under MIT License.
 *
 * This source file is subject to the MIT License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    evias/nem-php
 * @version    1.0.0
 * @author     Grégory Saive <greg@evias.be>
 * @license    MIT License
 * @copyright  (c) 2017-2018, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Traits;

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
     * This array can contain the keys "username"
     * and "password" and they will be arranged to
     * compose following format:
     *
     *     username:password@
     *
     * @see  \NEM\Traits\Connectable@getBasicAuth()
     * @var array   valid keys include: username, password
     */
    protected $basicAuth = [];

    /**
     * Setter for `use_ssl` property.
     *
     * @param  string $host
     * @return \NEM\API
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
     * @return \NEM\Contracts\HttpHandler
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
     * @return \NEM\API
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
     * @return \NEM\API
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
     * @return \NEM\API
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
     * Getter for `username` property.
     *
     * @return string
     */
    public function setUsername($username)
    {
        $this->basicAuth["username"] = $username;
        return $this;
    }

    /**
     * Getter for `username` property.
     *
     * @return string
     */
    public function getUsername()
    {
        if (! isset($this->basicAuth["username"]))
            return null;

        return $this->basicAuth["username"];
    }

    /**
     * Setter for `password` property.
     *
     * @return string
     */
    public function setPassword($password)
    {
        $this->basicAuth["password"] = $password;
        return $this;
    }

    /**
     * Getter for `password` property.
     *
     * @return string
     */
    public function getPassword()
    {
        if (! isset($this->basicAuth["password"]))
            return null;

        return $this->basicAuth["password"];
    }

    /**
     * This method returns the username:password@ format
     * string to perform Basic Authentication using the
     * URL.
     *
     * @return [type] [description]
     */
    public function getBasicAuth()
    {
        if (empty($this->basicAuth))
            return "";

        if (empty($this->basicAuth["username"]))
            // Username cannot be empty in Basic Authentication
            return "";

        $username = $this->basicAuth["username"];
        $password = isset($this->basicAuth["password"]) ? $this->basicAuth["password"] : "";
        return sprintf("%s:%s@", $username, $password);
    }

    /**
     * Getter for `base_url` property.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->getScheme() . $this->getBasicAuth() . $this->getHost() . ":" . $this->getPort() . $this->getEndpoint();
    }

    /**
     * Setter for the `base_url` property. This will create
     * a new Connectable object and return it.
     */
    public function setBaseUrl($baseUrl)
    {
        return $this->fillFromBaseUrl($baseUrl);
    }

    /**
     * Helper class method to create a Connectabl object from
     * a fully qualified base url.
     * 
     * @param   string  $baseUrl
     * @return  \NEM\Traits\Connectable
     */
    public function fillFromBaseUrl($baseUrl)
    {
        $reg = "/^(https?):\/\/((www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6})(:([0-9]+))?/";
        $matches = [];

        $scheme = "http";
        $host   = "hugealice.nem.ninja";
        $port   = 7890;
        if ((bool) preg_match_all($reg, $baseUrl, $matches)) {
            $scheme = $matches[1][0];
            $host   = $matches[2][0];
            $port   = $matches[5][0];
        }

        $this->setUseSsl($scheme === "https");
        $this->setProtocol($scheme);
        $this->setHost($host);
        $this->setPort((int) $port);
        return $this;
    }
}
