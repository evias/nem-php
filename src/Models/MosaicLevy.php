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

class MosaicLevy
    extends Model
{
    const TYPE_ABSOLUTE = 1;
    const TYPE_PERCENTILE = 2;

    /**
     * List of fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        "type",
        "recipient",
        "mosaicId",
        "fee",
    ];

    /**
     * The model instance's relations configuration
     *
     * @var array
     */
    protected $relations = [
        "mosaicId",
        "recipient",
    ];

    /**
     * List of automatic *value casts*.
     *
     * @var array
     */
    protected $casts = [
        "type" => "int",
        "fee"  => "int",
    ];

    /**
     * Mosaic Levy DTO builds a package with offsets `type`,
     * `recipient`, `mosaicId` and `fee`. 
     * 
     * Those fields are required by NIS for the mosaic levy identification.
     *
     * @return  array       Associative array with fields present in `$fillable` property.
     */
    public function toDTO($filterByKey = null)
    {
        if (! $this->getAttribute("recipient") || ! $this->getAttribute("mosaicId"))
            return [];

        return [
            "type" => $this->type,
            "recipient" => $this->recipient()->address()->toClean(),
            "mosaicId" => $this->mosaicId()->toDTO(),
            "fee" => $this->fee,
        ];
    }

    /**
     * Overload of the \NEM\Core\Model::serialize() method to provide
     * with a specialization for *Mosaic Levy* serialization.
     *
     * @see \NEM\Contracts\Serializable
     * @param   null|string $parameters    non-null will return only the named sub-dtos.
     * @return  array   Returns a byte-array with values in UInt8 representation.
     */
    public function serialize($parameters = null)
    {
        // shortcuts
        $serializer = $this->getSerializer();
        $nisData = $this->toDTO();

        if (empty($nisData)) {
            $emptyLevy = $serializer->serializeInt(null);
            return $serializer->aggregate($emptyLevy);
        }

        // serialize
        $type = $serializer->serializeInt($nisData["type"]);
        $recipient = $serializer->serializeString($nisData["recipient"]);
        $fee       = $serializer->serializeLong($nisData["fee"]);
        $mosaic    = $this->mosaicId()->serialize();

        // prepend size on 4 bytes + concatenate UInt8
        return $this->getSerializer()
                    ->aggregate($type, $recipient, $mosaic, $fee);
    }

    /**
     * Mutator for `mosaicId` relation.
     *
     * This will return a NIS compliant [MosaicId](https://bob.nem.ninja/docs/#mosaicId) object. 
     *
     * @param   array   $mosaidId       Array should contain offsets `namespaceId` and `name`.
     * @return  \NEM\Models\Mosaic
     */
    public function mosaicId(array $mosaicId = null)
    {
        return new Mosaic($mosaicId ?: $this->getAttribute("mosaicId"));
    }

    /**
     * Mutator for the recipient Account object.
     *
     * @return \NEM\Models\Account
     */
    public function recipient($address = null)
    {
        return new Account(["address" => $address ?: $this->getAttribute("recipient")]);
    }
}
