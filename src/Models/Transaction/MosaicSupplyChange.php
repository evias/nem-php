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
namespace NEM\Models\Transaction;

use NEM\Models\Transaction;
use NEM\Models\TransactionType;
use NEM\Models\Fee;
use NEM\Models\Mosaic;

/**
 * This is the MosaicSupplyChange class
 *
 * This class extends the NEM\Models\Transaction class
 * to provide with an integration of NEM's mosaic 
 * supply change transactions.
 * 
 * @link https://nemproject.github.io/#mosaicSupplyChangeTransaction
 */
class MosaicSupplyChange
    extends Transaction
{
    /**
     * NIS Mosaic Supply Types
     * 
     * @var integer
     */
    const TYPE_INCREASE = 1;
    const TYPE_DECREASE = 2;

    /**
     * List of additional fillable attributes
     *
     * @var array
     */
    protected $appends = [
        "mosaicId"      => "transaction.mosaicId",
        "supplyType"    => "transaction.supplyType",
        "delta"         => "transaction.delta",
    ];

    /**
     * The Signature transaction type does not need to add an offset to
     * the transaction base DTO.
     *
     * @return array
     */
    public function extend() 
    {
        // supply type validation
        $type = $this->getAttribute("supplyType");
        $validTypes = [self::TYPE_INCREASE, self::TYPE_DECREASE];
        if (! $type || ! in_array($type, $validTypes)) {
            $type = self::TYPE_INCREASE;
            $this->setAttribute("supplyType", $type);
        }

        // always positive delta
        $delta = abs($this->delta ?: 0);

        return [
            "mosaicId" => $this->mosaicId()->toDTO(),
            "supplyType" => $type,
            "delta" => $delta,
            // transaction type specialization
            "type" => TransactionType::MOSAIC_SUPPLY_CHANGE,
        ];
    }

    /**
     * The extendFee() method must be overloaded by any Transaction Type
     * which needs to extend the base FEE to a custom FEE.
     *
     * @return array
     */
    public function extendFee()
    {
        return Fee::NAMESPACE_AND_MOSAIC;
    }

    /**
     * Overload of the \NEM\Core\Model::serialize() method to provide
     * with a specialization for *MosaicSupplyChange* serialization.
     *
     * @see \NEM\Contracts\Serializable
     * @param   null|string $parameters    non-null will return only the named sub-dtos.
     * @return  array   Returns a byte-array with values in UInt8 representation.
     */
    public function serialize($parameters = null)
    {
        $baseTx  = parent::serialize($parameters);
        $nisData = $this->extend();

        // shortcuts
        $serializer = $this->getSerializer();
        $output     = [];

        // serialize specialized fields
        $uint8_mosaic = $this->mosaicId()->serialize();
        $uint8_supType = $serializer->serializeInt($nisData["supplyType"]);
        $uint8_delta   = $serializer->serializeLong($nisData["delta"]);

        // concatenate the UInt8 representation
        $output = array_merge(
            $uint8_mosaic,
            $uint8_supType,
            $uint8_delta);

        // specialized data is concatenated to `base transaction data`.
        return ($this->serialized = array_merge($baseTx, $output));
    }

    /**
     * Overload the constructor to set specialized casts.
     *
     * @see \NEM\Models\Model
     * @param   array   $attributes         Associative array where keys are attribute names and values are attribute values
     * @return  void
     */
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        // configure casts
        $this->casts["supplyType"] = "int";
        $this->casts["delta"] = "int";
    }

    /**
     * Mutator for `mosaic` relation.
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
}
