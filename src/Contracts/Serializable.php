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
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Contracts;

/**
 *
 * Interface to provide with easy Serialization.
 *
 * Serialization is best done when data can be represented
 * in binary form.
 *
 * This interface should with a simple way of getting a Buffer
 * created out of any class implementing the interface.
 *
 */
interface Serializable
{
    /**
     * Serializable::getBuffer()
     *
     * This method should return an object implementing 
     * \NEM\Core\Buffer such that the 
     * contents of the buffer can be represented as Binary
     * Data.
     *
     * @return \NEM\Core\Buffer
     */
    public function getBuffer();
}
