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
 * @version    0.1.0
 * @author     Grégory Saive <greg@evias.be>
 * @author     Robin Pedersen (https://github.com/RobertoSnap)
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/php-nem-laravel
 */
namespace NEM;

class SDK
{
    /**
     * The API wrapper instance.
     * 
     * @var \NEM\API
     */
    public $api;

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
    }

    /**
     * This __call hook makes sure calls to the SDK object
     * will always instatiate a class provided by the SDK.
     *
     * @example Example calls for \NEM\SDK
     *
     * $sdk = new SDK();
     * $sdk->models(); // will automatically craft \NEM\Infrastructure\Models
     * $sdk->network(); // will automatically craft \NEM\Infrastructure\Network
     *
     * @param  [type] $method    [description]
     * @param  array  $arguments [description]
     * @return [type]            [description]
     */
    public function __call($method, array $arguments)
    {
        if (method_exists($this, $method))
            // method overload exists, call it.
            return call_user_func_array([$this, $method], $arguments);

        // method does not exist, try to craft infrastructure class instance.

        // snake_case to camelCase
        $normalized = ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $method)), '_');
        $className  = ucfirst($normalized);
        $infraClass = "\\NEM\\Infrastructure\\" . $className;

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
     * @see \NEM\Models\Mutator
     * @return \NEM\Models\Mutator      The models mutator
     */
    public function models() 
    {
        return new ModelMutator();
    }
}
