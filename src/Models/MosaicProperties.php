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
namespace NEM\Models;

class MosaicProperties
    extends ModelCollection
{
    /**
     * Overload collection toDTO() to make sure to *always use the same sorting*.
     *
     * Mosaic Properties are ordered as described below:
     *
     * - divisibility
     * - initialSupply
     * - supplyMutable
     * - transferable
     *
     * The order of properties is not important for NIS but gives us consistency
     * in the way we can use mosaic properties.
     *
     * @see https://bob.nem.ninja/docs/#mosaicProperties  NIS MosaicProperties Documentation
     * @return  array       Array representation of the collection objects *compliable* with NIS definition.
     */
    public function toDTO() 
    {
        $props = [
            ["name" => "divisibility", "value" => null],
            ["name" => "initialSupply", "value" => null],
            ["name" => "supplyMutable", "value" => null],
            ["name" => "transferable", "value" => null],
        ];

        $propertiesNames = [
            "divisibility"  => 0,
            "initialSupply" => 1,
            "supplyMutable" => 2,
            "transferable"  => 3,
        ];

        foreach ($this->all() as $ix => $item) {
            // discover
            $index = $propertiesName[$item->name];

            // update mosaic property value.
            $props[$index]["value"] = $item->value;
        }

        return $props;
    }
}
