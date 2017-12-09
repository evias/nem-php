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
namespace NEM\Models;

use NEM\NemSDK;
use NEM\Models\Account\Account;
use NEM\Models\Account\Address;
use NEM\Models\Fee\Fee;
use NEM\Models\Mosaic\Mosaic;
use NEM\Models\Mosaic\Xem;
use NEM\Models\Namespaces\Namespaces;
use NEM\Models\Blockchain\Blockchain;
use NEM\Models\Transaction\Transaction;

class Model
    extends ArrayObject
{
    /**
     * List of fillable attributes
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * Construct a Model instance with attributes data.
     * 
     * @param   array   $attributes         Associative array where keys are attribute names and values are attribute values
     * @return  void
     */
    public function __construct(array $attributes = [])
    {
        $this->setAttributes($attributes);
    }

    /**
     * Setter for the `attributes` property.
     *
     * @return  \NEM\Models\Model
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = array_intersect_key($attributes, $this->fillable);
        return $this;
    }

    /**
     * Getter for the `attributes` property.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Helper for direct attributes/property access.
     *
     * @see ArrayObject
     * @param   string      $name   The property/attribute name.
     * @return  mixed
     */
    public function __get($name)
    {
        // attributes prevail over class properties
        if (array_key_exists($this->attributes, $name))
            return $this->attributes[$name];

        if ($this->offsetExists($name))
            return $this->offsetGet($name);

        return null;
    }

    /**
     * Helper for direct attributes/property setting.
     *
     * @see ArrayObject
     * @param   string      $name    The property/attribute name.
     * @param   mixed       $value   The new property/attribute value.
     * @return  mixed
     */
    public function __set($name, $value)
    {
        if ($this->offsetExists($name))
            // instance property available too
            $this->offsetSet($name, $value);

        // attributes prevail over class properties in __get()
        $this->attributes[$name] = $value;
        return $this->attributes[$name];
    }
}