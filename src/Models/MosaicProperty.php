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

class MosaicProperty
    extends Model
{
    /**
     * List of fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        "name",
        "value"
    ];

    /**
     * Address DTO automatically cleans address representation.
     *
     * @return  array       Associative array with key `address` containing a NIS *compliable* address representation.
     */
    public function toDTO($filterByKey = null)
    {
        $value = (string) $this->value;
        if (in_array($this->name, ["supplyMutable", "transferable"])) {
            $value = ($this->value !== "false" && (bool) $this->value) ? "true" : "false";
        }

        return [
            "name" => $this->name,
            "value" => $value,
        ];
    }

    /**
     * Overload of the \NEM\Core\Model::serialize() method to provide
     * with a specialization for *Mosaic Definition Properties* serialization.
     *
     * @see \NEM\Contracts\Serializable
     * @param   null|string $parameters    non-null will return only the named sub-dtos.
     * @return  array   Returns a byte-array with values in UInt8 representation.
     */
    public function serialize($parameters = null)
    {
        $nisData = $this->toDTO();

        // shortcuts
        $serializer    = $this->getSerializer();
        $serializedName = $serializer->serializeString($nisData["name"]);
        $serializedValue = $serializer->serializeString($nisData["value"]);

        return $serializer->aggregate($serializedName, $serializedValue);
    }
}
