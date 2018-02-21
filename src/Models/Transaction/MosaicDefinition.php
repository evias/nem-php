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
use NEM\Models\Account;
use NEM\Models\MosaicDefinition as DefinitionModel;

class MosaicDefinition
    extends Transaction
{
    /**
     * List of additional fillable attributes
     *
     * @var array
     */
    protected $appends = [
        "mosaicDefinition"   => "transaction.mosaicDefinition",
        "creationFeeSink"    => "transaction.creationFeeSink",
        "creationFee"        => "transaction.creationFee",
    ];

    /**
     * The mosaic creation Fee Sinks.
     *
     * @var array
     */
    protected $sinks = [
        "testnet" => "TBMOSAICOD4F54EE5CDMR23CCBGOAM2XSJBR5OLC",
        "mainnet" => "NBMOSAICOD4F54EE5CDMR23CCBGOAM2XSIUX6TRS"
    ];

    /**
     * The extend() method must be overloaded by any Transaction Type
     * which needs to extend the base DTO structure.
     *
     * @return array
     */
    public function extend() 
    {
        return [
            "creationFeeSink" => $this->creationFeeSink()->address()->toClean(),
            "creationFee" => Fee::MOSAIC_DEFINITION,
            "mosaicDefinition" => $this->mosaicDefinition()->toDTO(),
            // transaction type specialization
            "type"      => TransactionType::MOSAIC_DEFINITION,
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
        // careful here, the `Fee::MOSAIC_DEFINITION` constant
        // defines the `creationFee`, not the transaction fee.
        return Fee::NAMESPACE_AND_MOSAIC;
    }

    /**
     * Mutator for the `creationFeeSink` relation.
     *
     * @return  \NEM\Models\Account
     */
    public function creationFeeSink($address = null)
    {
        return new Account(["address" => $address ?: $this->getAttribute("creationFeeSink")]);
    }

    /**
     * Mutator for the `mosaicDefinition` relation.
     *
     * This will return a NIS compliant [MosaicDefinition](https://bob.nem.ninja/docs/#mosaicDefinition) object. 
     *
     * @param   array   $definition       Array should contain offsets `creator`, `id`, `description`, `properties` and `levy`.
     * @return  \NEM\Models\MosaicDefinition
     */
    public function mosaicDefinition(array $definition = null)
    {
        return new DefinitionModel($definition ?: $this->getAttribute("mosaicDefinition"));
    }
}
