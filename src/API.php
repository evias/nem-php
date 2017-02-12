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
namespace evias\NEMBlockchain;

use RuntimeException;
use InvalidArgumentException;

/**
 * This is the NEMBlockchain\API class
 *
 * This class should provide the gateway for processing
 * API requests and sending to NIS or NCC API clients.
 *
 * @see  \evias\NEMBlockchain\Contracts\Connector
 * @see  \evias\NEMBlockchain\Traits\Connectable
 * @author Grégory Saive <greg@evias.be>
 */
class API
{
    /**
     * The Laravel/Lumen IoC container
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $app;

    /**
     * The class used for handling HTTP requests.
     *
     * This class must implement the HttpHandler
     * contract.
     *
     * @var string
     */
    protected $handlerClass = \evias\NEMBlockchain\Handlers\UnirestHttpHandler::class;

    /**
     * The request handler use to send API calls over
     * HTTP/JSON to NIS or NCC endpoints.
     *
     * @var \evias\NEMBlockchain\Contracts\HttpHandler
     */
    protected $requestHandler;

    /**
     * Constructor for a new NEMBlockchain\API instance.
     *
     * This will initialize the Laravel/Lumen IoC.
     *
     * @param Container $app [description]
     */
    public function __construct()
    {
    }

    /**
     * This method allows to set the API configuration
     * through an array rather than using the Laravel
     * and Lumen Config contracts.
     *
     * @param  array $options
     * @return \evias\NEMBlockchain\API
     * @throws InvalidArgumentException on invalid option names.
     */
    public function setOptions(array $options)
    {
        foreach ($options as $option => $config) {
            if (! (bool) preg_match("/^[0-9A-Za-z_]+$/", $option))
                // invalid option format
                throw new InvalidArgumentException("Invalid option name provided to evias\\NEMBlockchain\\API@setOptions: " . var_export($option, true));

            $upper  = str_replace(" ", "", ucwords(str_replace("_", " ", $option)));
            $setter = "set" . $upper;

            if (method_exists($this, $setter))
                $this->$setter($config);
            elseif (method_exists($this->getRequestHandler(), $setter))
                $this->getRequestHandler()->$setter($config);
        }

        return $this;
    }

    /**
     * Set the linked laravel/lumen Application
     * class instance.
     *
     * @param Application $app
     */
    public function setApplication(Application $app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * Return the linked laravel/lumen Application
     * class instance.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function getApplication()
    {
        return $this->app;
    }

    /**
     * Setter for `handlerClass` property.
     *
     * This property is used for instantiating the
     * HTTP handler object.
     *
     * @param  string $class
     * @return \evias\NEMBlockchain\API
     */
    public function setHandlerClass($class)
    {
        $this->handlerClass = $class;
        return $this;
    }

    /**
     * Getter for the `handlerClass` property.
     *
     * @return string
     */
    public function getHandlerClass()
    {
        return $this->handlerClass;
    }

    /**
     * Set the HttpHandler to use as this API
     * instance's request handler.
     *
     * @param HttpHandler $handler [description]
     */
    public function setRequestHandler(HttpHandler $handler)
    {
        $this->requestHandler = $handler;
        return $this;
    }

    /**
     * The getRequestHandler method creates an instance of the
     * `handlerClass` and returns it.
     *
     * @return \evias\NEMBlockchain\Contracts\HttpHandler
     */
    public function getRequestHandler()
    {
        if (isset($this->requestHandler))
            return $this->requestHandler;

        // now instantiating handler from config
        $handlerClass = "\\" . ltrim($this->handlerClass, "\\");
        if (!class_exists($handlerClass))
            throw new RuntimeException("Unable to create HTTP Handler instance with class: " . var_export($handlerClass, true));

        $this->requestHandler = new $handlerClass();
        return $this->requestHandler;
    }

    /**
     * This __call hook makes sure calls to Request Handlers
     * methods get forwarded and also forwards methods like
     * "getJSON" and "postJSON" so that it will directly
     * return the JSON (string) from the response of the
     * Request Handler.
     *
     * @param  [type] $method    [description]
     * @param  array  $arguments [description]
     * @return [type]            [description]
     */
    public function __call($method, array $arguments)
    {
        if (method_exists($this->getRequestHandler(), $method))
            // simple method call forwarding
            return call_user_func_array([$this->getRequestHandler(), $method], $arguments);

        // if user wants "JSON"-ending method, it is possible that he is
        // specifying that we should return the JSON directly.
        if (false !== strpos($method, "JSON")) {
            $proxyCall = [];
            preg_match_all("/([a-zA-Z0-9]+)JSON$/", $method, $proxyCall);

            if (empty($proxyCall[1]) || empty($proxyCall[1][0]))
                // invalid method name
                throw new BadMethodCallException("Method '" . $method . "' is not defined in evias\\NEMBlockchain\\API and could not forwarded.");

            $realMethod = $proxyCall[1][0];
            if (! method_exists($this->getRequestHandler(), $realMethod))
                // after remove of "JSON" in the name, the method still doesn't exist
                throw new BadMethodCallException("Method '" . $realMethod . "' is not defined in evias\\NEMBlockchain\\Handlers\\" . get_class($this->getRequestHandler()) . " and could not forwarded.");

            // valid forwarding applied (took the "JSON" part away)
            // get response from request handler and return JSON body.
            $response = call_user_func_array([$this->getRequestHandler(), $realMethod], $arguments);
            return (string) $response->getBody();
        }

        throw new BadMethodCallException("Method '" . $method . "' is not defined in evias\\NEMBlockchain\\API.");
    }
}
