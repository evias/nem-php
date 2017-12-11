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
 * @author     Robin Pedersen (https://github.com/RobertoSnap)
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/php-nem-laravel
 */
namespace NEM;

use NEM\Models\Mutators\ModelMutator;

class SDK
{
    /**
     * The API wrapper instance.
     *
     * @var \NEM\API
     */
    protected $api;

    /**
     * The model mutator.
     *
     * @var \NEM\Models\ModelMutator
     */
    protected $models;

    /**
     * Construct a SDK object.
     *
     * @see \NEM\API::__construct()
     * @param   array   $options    Options array passed to NEM\API
     * @return  void
     */
    public function __construct($options = []) 
    {
        $this->api = new API($options);
        $this->models = new ModelMutator();
    }

    /**
     * Setter for the currently used NIS API Wrapper.
     *
     * This lets the developer overwrite the currently used NEM NIS API Wrapper.
     *
     * @param   \NEM\API    $api    An initialized NIS API instance.
     * @return  \NEM\SDK
     */
    public function setNISWrapper(API $api)
    {
        $this->api = $api;
        return $this;
    }

    /**
     * Getter for the currently used NIS API Wrapper.
     *
     * @param   \NEM\API    $api    An initialized NIS API instance.
     * @return  \NEM\SDK
     */
    public function getNISWrapper()
    {
        return $this->api;
    }

    /**
     * This __call hook makes sure calls to the SDK object
     * will always instatiate a class provided by the SDK.
     *
     * @example Example calls for \NEM\SDK
     *
     * $sdk = new SDK();
     * $sdk->account(); // will automatically craft service instance for \NEM\Infrastructure\Account
     * $sdk->network(); // will automatically craft service instance for \NEM\Infrastructure\Network
     *
     * @param  string $method
     * @param  array  $arguments
     * @return mixed|\NEM\Infrastructure\ServiceInterface
     */
    public function __call($method, array $arguments)
    {
        if (method_exists($this, $method))
            // method overload exists, call it.
            return call_user_func_array([$this, $method], $arguments);

        // method does not exist, try to craft infrastructure class instance.

        // snake_case to CamelCase
        $infraClass = "\\NEM\\Infrastructure\\" . Str::studly($method);

        if (!class_exists($infraClass)) {
            throw new BadMethodCallException("Infrastructure class '" . $infraClass . "' could not be found in \\NEM\\Infrastructure namespace.");
        }

        $instance = new $infraClass($this->api);
        return $instance;
    }

    /**
     * The models() method should implement an easy to use models mutator for the 
     * SDK. This will help creating NEM compatible objects.
     *
     * @example Example calls for \NEM\Models\ModelMutator
     *
     * $sdk = new SDK();
     * $sdk->models()->address(); // will automatically craft a \NEM\Models\Address object
     * $sdk->models()->namespace(); // will automatically craft a \NEM\Models\Namespace object
     *
     * @example Example from Data Transfer Object for \NEM\Models\ModelMutator
     *
     * $sdk = new SDK();
     * $address = $sdk->models()->address(["address" => "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ"]);
     *
     * echo $address->address; // "TDWZ55R5VIHSH5WWK6CEGAIP7D35XVFZ3RU2S5UQ"
     * echo $address->toPretty(); // "TDWZ55-R5VIHS-H5WWK6-CEGAIP-7D35XV-FZ3RU2-S5UQ"
     *
     * @see \NEM\Models\Mutator
     * @return \NEM\Models\Mutator      The models mutator
     */
    public function models() 
    {
        return new ModelMutator();
    }
}
