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

use Illuminate\Contracts\Container\Container;

/**
 * This is the NEMBlockchain\API class
 *
 * This class should provide the gateway for processing
 * API requests and sending to NIS or NCC API clients.
 *
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
     * The request handler use to send API calls over
     * HTTP/JSON to NIS or NCC endpoints.
     *
     * @var \evias\NEMBlockchain\Contracts\HttpHandler
     */
    protected $requestHandler;

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
     * Constructor for a new NEMBlockchain\API instance.
     *
     * This will initialize the Laravel/Lumen IoC.
     *
     * @param Container $app [description]
     */
    public function __construct(Container $app)
    {
        $this->app = $app;

        if (false !== strpos($this->app->version(), "Laravel")
            || false !== strpos($this->app->version(), "Lumen"))
            // lumen or laravel, use config() helper
            $this->handlerClass = config("nem.handler_class");
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
            if (! (bool) preg_match("/^[0-9A-Za-z_]+$/"))
                // invalid option format
                throw new InvalidArgumentException("Invalid option name provided to evias\\NEMBlockchain\\API@setOptions: " . var_export($option, true));

            $normalized = str_replace(" ", "", ucwords(str_replace("_", " ", $option)));

            if (method_exists($this, $normalized))
                $this->$normalized($config);
        }

        return $this;
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
}
