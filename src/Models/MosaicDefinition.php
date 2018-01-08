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

class MosaicDefinition
    extends Model
{
    /**
     * List of fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        "creator",
        "id",
        "description",
        "properties",
        "levy",
    ];

    /**
     * The model instance's relations configuration
     *
     * @var array
     */
    protected $relations = [
        "id",
        "properties",
        "levy",
    ];

    /**
     * Address DTO automatically cleans address representation.
     *
     * @return  array       Associative array with key `address` containing a NIS *compliable* address representation.
     */
    public function toDTO($filterByKey = null)
    {
        return [
            "creator" => $this->creator,
            "id" => $this->mosaic()->toDTO(),
            "description" => $this->description,
            "properties" => $this->properties()->toDTO(),
            "levy" => $this->levy()->toDTO(),
        ];
    }

    /**
     * Mutator for `mosaic` relation.
     *
     * This will return a NIS compliant [MosaicId](https://bob.nem.ninja/docs/#mosaicId) object. 
     *
     * @param   array   $mosaidId       Array should contain offsets `namespaceId` and `name`.
     * @return  \NEM\Models\Mosaic
     */
    public function id(array $mosaicId = null)
    {
        return new Mosaic($mosaicId ?: $this->getAttribute("mosaicId"));
    }

    /**
     * Mutator for `levy` relation.
     *
     * This will return a NIS compliant [MosaicLevy](https://bob.nem.ninja/docs/#mosaicLevy) object. 
     *
     * @param   array   $mosaidId       Array should contain offsets `type`, `recipient`, `mosaicId` and `fee`.
     * @return  \NEM\Models\MosaicLevy
     */
    public function levy(array $levy = null)
    {
        return new MosaicLevy($levy ?: $this->getAttribute("levy"));
    }

    /**
     * Mutator for `properties` relation.
     *
     * This will return a NIS compliant collection of [MosaicProperties](https://bob.nem.ninja/docs/#mosaicProperties) object. 
     *
     * @param   array   $properties       Array of MosaicProperty instances
     * @return  \NEM\Models\MosaicProperties
     */
    public function properties(array $properties = null)
    {
        return MosaicProperties($properties ?: $this->getAttribute("properties"));
    }

    /**
     * Helper to read a given `name` mosaic property name.
     * 
     * @param   string  $name       Mosaic property name.
     * @return  integer|boolean
     */
    public function getProperty($name)
    {
        $propertiesNames = [
            "divisibility"  => 0,
            "initialSupply" => 1,
            "supplyMutable" => 2,
            "transferable"  => 3,
        ];

        if (! array_key_exists($name, $propertiesNames)) {
            throw new InvalidArgumentException("Mosaic property name '" . $name ."' is invalid. Must be one of 'divisibility', "
                                             . "'initialSupply', 'supplyMutable' or 'transferable'");
        }

        $index = $propertiesNames[$name];
        $value = $this->properties()->get($index)->value;
        return $value;
    }
}
