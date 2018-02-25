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
 * @copyright  (c) 2017-2018, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Contracts;

/**
 * This is the DataTransferObject interface
 *
 * This interface defines a Contract for generic
 * NIS compliant objects.
 *
 * A "DTO" defines `attributes` and a `toDTO()`
 * method used easy formatting in NIS compliant
 * formats.
 */
interface DataTransferObject
{
    /**
     * Setter for the `attributes` property.
     *
     * @return  \NEM\Models\ModelInterface
     */
    public function setAttributes(array $attributes);

    /**
     * Getter for the `attributes` property.
     *
     * @return array
     */
    public function getAttributes();

    /**
     * Helper method to build NIS compliant Data Transfer
     * Objects.
     *
     * @return array
     */
    public function toDTO($filterByKey = null);
}
