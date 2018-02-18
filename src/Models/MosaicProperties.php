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
        // sort properties lexicographically (see toDTO() overload)
        $sorted = $this->sortBy("name");
        $props = [];

        foreach ($this->all() as $ix => $item) {
            // discover
            $name  = $item->getAttribute("name");
            $value = (string) $item->value;
        
            if (in_array($name, ["supplyMutable", "transferable"])) {
                $value = ((bool) $item->value) ? "true" : "false";
            }

            // update mosaic property value.
            $props[$ix] = [
                "name" => $name,
                "value" => $value,
            ];
        }

        return $props;
    }

    /**
     * Overload of the \NEM\Core\ModelCollection::serialize() method to provide
     * with a specialization for *MosaicProperties Arrays* serialization.
     *
     * @see \NEM\Contracts\Serializable
     * @param   null|string $parameters    non-null will return only the named sub-dtos.
     * @return  array   Returns a byte-array with values in UInt8 representation.
     */
    public function serialize($parameters = null)
    {
        // shortcuts
        $serializer = $this->getSerializer();

        // sort properties lexicographically (see toDTO() overload)
        $sorted = $this->sortBy("name");

        // serialize attachments
        // prepend size on 4 bytes
        $prependSize = $serializer->serializeInt($sorted->count());

        // serialize each attachment
        $stateUInt8 = $prependSize;
        foreach ($sorted->all() as $property) {
            if (is_array($property)) {
                $property = new MosaicProperty($property);
            }

            // use MosaicProperty::serialize() specialization
            $serialized = $property->serialize();

            // use merge here, no aggregator
            $stateUInt8 = array_merge($stateUInt8, $serialized);
        }

        // no need to use the aggregator, we dynamically aggregated
        // our collection data and prepended the size on 4 bytes.
        return $stateUInt8;
    }
}
