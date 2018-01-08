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
 * @author     Robin Pedersen (https://github.com/RobertoSnap)
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Models\Mutators;

use Illuminate\Support\Str;
use BadMethodCallException;

class ModelMutator
{
    /**
     * Mutate a Model object.
     *
     * This method takes a *snake_case* model name and converts it
     * to a class name in the namespace \NEM\Models.
     *
     * @internal
     * @param  string   $name           The model name you would like to create.
     * @param  array    $attributes     The model's attribute values.
     * @return \NEM\Models\ModelInterface
     */
    public function mutate($name, $attributes)
    {
        // snake_case to camelCase
        $modelClass = "\\NEM\\Models\\" . Str::studly($name);

        if (!class_exists($modelClass)) {
            throw new BadMethodCallException("Model class '" . $modelClass . "' could not be found in \\NEM\\Model namespace.");
        }

        //XXX add fields list to Models
        $instance = new $modelClass($attributes);
        return $instance;
    }

    /**
     * This __call hook makes sure calls to the Mutator object
     * will always instantiate a Models class provided by the SDK.
     *
     * @example Example *method* calls for \NEM\Models\ModelMutator
     *
     * $sdk = new SDK();
     * $sdk->models()->address(["address" => "NB72EM6TTSX72O47T3GQFL345AB5WYKIDODKPPYW"]); // will automatically craft a \NEM\Models\Address object
     * $sdk->models()->namespace(["namespace" => "evias"]); // will automatically craft a \NEM\Models\Namespace object
     *
     * @example Example building \NEM\Models\Model objects with the ModelMutator
     *
     * $sdk = new SDK();
     * $addr = $sdk->models()->address();
     * $addr->address = "NB72EM6TTSX72O47T3GQFL345AB5WYKIDODKPPYW";
     * var_dump($addr->toDTO()); // will contain address field
     *
     * @internal
     * @param  string   $name           The model name you would like to create.
     * @param  array    $attributes     The model's attribute values.
     * @return \NEM\Models\ModelInterface
     */
    public function __call($name, array $arguments)
    {
        if (method_exists($this, $name))
            // method overload exists, call it.
            return call_user_func_array([$this, $name], $arguments);

        // method does not exist, try to craft model class instance.
        return $this->mutate($name, $arguments);
    }
}