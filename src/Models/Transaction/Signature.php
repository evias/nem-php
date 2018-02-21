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

class Signature
    extends Transaction
{
    /**
     * List of additional fillable attributes
     *
     * @var array
     */
    protected $appends = [
        "otherHash",
        "otherAccount",
    ];

    /**
     * The Signature transaction type does not need to add an offset to
     * the transaction base DTO.
     *
     * @return array
     */
    public function extend() 
    {
        return [
            "otherHash" => [
                "data" => $this->getAttribute("otherHash"),
            ],
            "otherAccount" => $this->otherAccount()->address()->toClean(),
            // transaction type specialization
            "type" => TransactionType::MULTISIG_SIGNATURE,
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
        return floor(Fee::SIGNATURE / pow(10, 6));
    }

    /**
     * Mutator for the `otherAccount` relationship.
     * 
     * @param   string      $address
     */
    public function otherAccount($address = null)
    {
        return new Account($address ?: $this->getAttribute("otherAccount"));
    }
}
