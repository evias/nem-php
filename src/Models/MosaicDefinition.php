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
        "creator",
        "properties",
        "levy",
        "description",
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
            "description" => $this->description()->getPlain(),
            "properties" => $this->properties()->toDTO(),
            "levy" => $this->levy()->toDTO(),
        ];
    }

    /**
     * Overload of the \NEM\Core\Model::serialize() method to provide
     * with a specialization for *Mosaic Definition* serialization.
     *
     * @see \NEM\Contracts\Serializable
     * @param   null|string $parameters    non-null will return only the named sub-dtos.
     * @return  array   Returns a byte-array with values in UInt8 representation.
     */
    public function serialize($parameters = null)
    {
        // shortcuts
        $serializer = $this->getSerializer();
        $publicKey  = hex2bin($this->creator()->publicKey);

        // bundle with length of pub key and public key in UInt8
        $publicKey  = $serializer->serializeString($publicKey);
        //dd($this->description());
        // serialize content
        // [64, 0, 0, 0, 6, 8, 7, 4, 7, 4, 7, 0, 7, 3, 3, 0, 2, 0, 2, 0, 6, 7, 6, 9, 7, 4, 6, 8, 7, 5, 6, 2, 2, 0, 6, 3, 6, 0, 6, 0, 2, 0, 6, 5, 7, 6, 6, 9, 6, 1, 7, 3, 2, 0, 6, 0, 6, 5, 6, 0, 2, 0, 7, 0, 6, 8, 7, 0]
        $desc   = $serializer->serializeString(hex2bin($this->description()->toHex()));
        //dd(json_encode($desc));
        $mosaic = $this->id()->serialize();
        $props  = $this->properties()->serialize();
        $levy   = null === $this->levy() ? $serializer->serializeInt(0)
                                         : $this->levy()->serialize();

        // concatenate UInt8
        $output = array_merge($publicKey, $mosaic, $desc, $props, $levy);

        // do not use aggregator because MosaicDefinition's first byte
        // contains a public key size, not a DTO size.
        return $output;
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
     * Mutator for the recipient Account object.
     *
     * @return \NEM\Models\Account
     */
    public function creator($publicKey = null)
    {
        return new Account(["publicKey" => $publicKey ?: $this->getAttribute("creator")]);
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
        $levy = new MosaicLevy($levy ?: $this->getAttribute("levy"));
        $attribs = $levy->getAttributes();
        return empty($attribs) ? null : $levy;
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
        return new MosaicProperties($properties ?: $this->getAttribute("properties"));
    }

    /**
     * Mutator for `description` relation.
     *
     * @param   array   $mosaidId       Array should contain offsets `namespaceId` and `name`.
     * @return  \NEM\Models\Mosaic
     */
    public function description($description = null)
    {
        $msg = new Message();
        $msg->setPlain($description);

        return $msg;
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
