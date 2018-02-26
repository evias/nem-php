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
namespace NEM\Contracts;

/**
 * This is the Serializable interface
 *
 * This interface defines a Contract for serialized
 * NIS objects.
 *
 * Serialized objects are expressed in UInt8 using
 * the \NEM\Core\Serializer class.
 */
interface Serializable
{
    /**
     * Serializable::serialize()
     * 
     * This method should return a *byte-array* with UInt8
     * representation of bytes for the said object.
     * 
     * Each class implementing this interface should provide
     * with a specific *serializing process* where the data
     * is grouped and organized correctly according to the 
     * NIS reference.
     * 
     * @param   null|mixed  $parameters     Any parameters you want to pass along to the serialization process.
     * @return  array   Returns a byte-array with values in UInt8 representation.
     */
    public function serialize($parameters = null);
}
