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
use NEM\Models\Model;
use NEM\Core\Buffer;
use NEM\Models\MultisigModifications;
use NEM\Models\MultisigModification;
use NEM\Core\Serializer;

class MultisigAggregateModification
    extends Transaction
{

    /**
     * List of automatic *value casts*.
     *
     * @var array
     */
    protected $casts = [
        "relativeChange" => "int",
    ];

    /**
     * List of additional fillable attributes
     *
     * @var array
     */
    protected $appends = [
        "modifications"     => "transaction.modifications",
        "minCosignatories"  => "transaction.minCosignatories",
        "relativeChange"    => "transaction.minCosignatories.relativeChange",
    ];

    /**
     * Overload of the \NEM\Core\Model::serialize() method to provide
     * with a specialization for *MultisigAggregateModification* serialization.
     *
     * @see \NEM\Contracts\Serializable
     * @param   null|string $parameters    non-null will return only the named sub-dtos.
     * @return  array   Returns a byte-array with values in UInt8 representation.
     */
    public function serialize($parameters = null)
    {
        $baseTx  = parent::serialize($parameters);
        $nisData = $this->toDTO("transaction");

        // shortcuts
        $serializer = $this->getSerializer();
        $output     = [];

        $mapped = $this->modifications()->map(function($modification) {
            return new MultisigModification($modification);
        });

        // serialize specialized fields
        $uint8_size = $serializer->serializeInt(count($nisData["modifications"]));
        $uint8_mods = [];
        foreach ($mapped->all() as $modification) {
            $uint8_mod  = $modification->serialize($parameters);
            $uint8_mods = array_merge($uint8_mods, $uint8_mod);
        }

        // serialize relativeChange only for transaction.version > v2
        $uint8_change = [];
        $version = $nisData["version"];

        // XXX should not use v2 constants but arithmetic test
        if (in_array($version, [Transaction::VERSION_2,
                                Transaction::VERSION_2_TEST,
                                Transaction::VERSION_2_MIJIN])) {

            $change = (int) $nisData["minCosignatories"]["relativeChange"];
            if ($change < 0) {
                // apply sentinel to negative numbers
                $change = Serializer::NULL_SENTINEL & $change;
            }

            // size-security through Buffer class
            $chBuf  = Buffer::fromInt($change, 4, null, Buffer::PAD_LEFT);
            $uint8_change = array_reverse($chBuf->toUInt8());
            $uint8_lenCh  = $serializer->serializeInt(4);

            // prefix serialized size
            $uint8_change = array_merge($uint8_lenCh, $uint8_change);
        }
        // else {
        // v1 does not have multisig *modifications* (no relativeChange possible)
        // }

        // concatenate the UInt8 representation
        $output = array_merge(
            $uint8_size,
            $uint8_mods,
            $uint8_change);

        // specialized data is concatenated to `base transaction data`.
        return array_merge($baseTx, $output);
    }

    /**
     * The Signature transaction type does not need to add an offset to
     * the transaction base DTO.
     *
     * @return array
     */
    public function extend() 
    {
        return [
            "modifications" => $this->modifications()->toDTO(),
            "minCosignatories" => $this->minCosignatories()->toDTO(),
            // transaction type specialization
            "type" => TransactionType::MULTISIG_MODIFICATION,
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
        return Fee::MULTISIG_AGGREGATE_MODIFICATION;
    }

    /**
     * Mutator for the modifications collection.
     *
     * @return \NEM\Models\ModelCollection
     */
    public function modifications(array $modifications = null)
    {
        $mods = $modifications ?: $this->getAttribute("modifications") ?: [];
        return new MultisigModifications($mods);
    }

    /**
     * Mutator for the modifications collection.
     *
     * @return \NEM\Models\ModelCollection
     */
    public function minCosignatories($minCosignatories = null)
    {
        $minCosignatories = $minCosignatories ?: $this->getAttribute("minCosignatories") ?: $this->relativeChange;

        // "minCosignatories" can be used as direct attribute setter
        if (is_integer($minCosignatories)) {
            $relativeChange = $minCosignatories;
        }
        // "minCosignatories" is also a sub-dto
        elseif (is_array($minCosignatories) && isset($minCosignatories["relativeChange"])) {
            $relativeChange = $minCosignatories["relativeChange"];
        }
        else {
            $relativeChange = $this->relativeChange ?: 0;
        }

        $this->setAttribute("relativeChange", $relativeChange);
        return new Model(["relativeChange" => $this->relativeChange]);
    }
}
