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
use NEM\Models\Fee;

class MosaicDefinition
    extends Transaction
{
    /**
     * List of additional fillable attributes
     *
     * @var array
     */
    protected $appends = [
        "mosaicDefinition",
        "creationFeeSink",
        "creationFee",
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
            "mosaicDefinition" => $this->mosaicDefinition()->toDTO()
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
        return Fee::MOSAIC_DEFINITION;
    }

    /**
     * Mutator for the `creationFeeSink` relation.
     *
     * @return  \NEM\Models\Account
     */
    public function creationFeeSink($address = null)
    {
        return new Account($address ?: $this->getAttribute("creationFeeSink"));
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
        return new MosaicDefinition($definition ?: $this->getAttribute("mosaicDefinition"));
    }
}
