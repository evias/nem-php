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
namespace NEM\Models;

use Nem\Infrastructure\ServiceInterface;

class Model
    extends ArrayObject
    implements ModelInterface
{
    /**
     * List of fillable attributes
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The model instance's attribute values.
     *
     * @var array
     */
    protected $attributes = [];

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
    public function &__get($name)
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

    /**
     * Check whether or not a data key exists by name.
     *
     * @param   string  $name   A data name to check for
     * @return  boolean
     */
    public function __isset($name) {
        return isset($this->attributes[$name]);
    }

    /**
     * Unsets an data key by name.
     *
     * @param   string  $name   A data name to check for
     * @return  void
     */
    public function __unset($name) 
    {
        unset($this->attributes[$name]);
    }
}